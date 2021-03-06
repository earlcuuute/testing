<?php

namespace App\Http\Controllers;

use App\Question;
use App\QuestionChoice;
use App\QuestionOption;
use App\QuestionType;
use App\Repositories\SurveyRepository;
use App\Response;
use App\Survey;
use App\SurveyOption;
use App\SurveyPage;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\SurveyCategory;
use App\Http\Requests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Repositories\TaskRepository;
use Illuminate\Support\Facades\View;
use Log;

class SurveyController extends Controller
{
    protected $surveys;

    public function __construct(SurveyRepository $surveys)
    {
        $this->middleware('auth');

        $this->surveys = $surveys;
    }

    public function index()
    {
        return view('survey.create', [
            'categories' => $this->getSurveyCategories(),
            'surveys' => Auth::user()->surveys->where('is_template', 0),
            'templates' => Survey::where('is_template', 1)->get()
        ]);
    }

    public function create(Request $request)
    {

        Log::info($request);
        if($request->create_from == "existing"){
            $this->validate($request,[
                'existing_survey' => 'required',
            ]);

            $survey = Survey::find($request->existing_survey);
            $newSurvey = $survey->replicate();
            if($request->new_title != null){
                $newSurvey->new_title = $request->new_title;
            }
            $newSurvey->published = false;
            $newSurvey->save();
            foreach ($survey->pages as $page){
                $newPage = $page->replicate();
                $newPage->survey_id = $newSurvey->id;
                $newPage->save();
                $this->replicateQuestions($page, $newPage);
            }

            return redirect('/create/' .$newSurvey->id);
        }elseif($request->create_from == "scratch"){
            $this->validate($request,[
                'survey_title' => 'required|max:250',
                'category' => $request->is_template ? 'required' : 'max:250',
            ]);

//            if($request->is_template){
//                $this->validate($request,[
//                    'category' => 'required',
//                ]);
//            }

            $survey = new Survey;

            DB::transaction(function() use ($request, $survey){
                $category = $request->category;
                if($request->category == -1){
                    $category = null;
                }

                $survey->survey_title = $request->survey_title;
                $survey->user()->associate($request->user());
                $survey->category()->associate($category);
                if($request->is_template){
                    $survey->is_template = true;
                }
                $survey->save();

                $survey->pages()->create([
                    'page_no' => 1,
                ]);

            });
            if($request->is_template){
                return redirect('admin/templates/create/' .$survey->id);
            }else{
                return redirect('/create/' .$survey->id);
            }
        }elseif($request->create_from == "template"){
            $this->validate($request,[
                'survey_template' => 'required',
            ]);

            $survey = Survey::find($request->survey_template);
            $newSurvey = $survey->replicate();
            $newSurvey->user()->associate($request->user());
            $newSurvey->published = false;
            $newSurvey->is_template = false;
            if($request->is_template){
                $survey->is_template = true;
            }
            $newSurvey->save();
            foreach ($survey->pages as $page){
                $newPage = $page->replicate();
                $newPage->survey_id = $newSurvey->id;
                $newPage->save();
                $this->replicateQuestions($page, $newPage);
            }

            if($request->is_template){
                return redirect('admin/templates/create/' .$survey->id);
            }
            return redirect('/create/' .$newSurvey->id);
        }


    }

    public function store($id, Request $request) //storing the question
    {
        $this->validate($request, [
            'question_title' => 'required|max:250',
            'question_type' => 'required',
            'page_id' => 'required'
        ]);

        $survey = Survey::find($id);
        $page = SurveyPage::find($request->page_id);
        $type = QuestionType::find($request->question_type);

        switch ($request->manipulation_method){
            case "add":
                $question = new Question();
                $question->question_title = $request->question_title;
                $question->is_mandatory = $request->is_mandatory;

                //INCREMENT ORDER NUMBER
                $latestQuestion = $page->questions()->orderBy('order_no', 'desc')->first();
                $question->order_no = $latestQuestion == null ? 1 : $latestQuestion->order_no + 1;

                $question->surveyPage()->associate($page);
                $question->questiontype()->associate($type);



                DB::transaction(function() use($request, $question, $type){
                    $question->save();
                    if($type->type == "Rating Scale"){
                        $option = new QuestionOption();
                        $option->max_rating = $request->max_rating;
                        $option->question()->associate($question);
                        $option->save();
                    }
                    $this->saveChoices($type, $question, $request->choices);
                });
                break;

            case "edit":
                $question = Question::find($request->question_id);
                DB::transaction(function() use($request, $question, $type){
                    QuestionChoice::where('question_id', $request->question_id)->delete();
                    QuestionOption::where('question_id', $request->question_id)->delete();
                    $question->update([
                        'question_title' => $request->question_title,
                        'question_type_id' => $request->question_type,
                        'is_mandatory' => $request->is_mandatory,
                    ]);
                    if($type->type == "Rating Scale"){
                        $option = new QuestionOption();
                        $option->max_rating = $request->max_rating;
                        $option->question()->associate($question);
                        $option->save();
                    }
                    $this->saveChoices($type, $question, $request->choices);
                });
                break;
        }

//        $this->updateSurveyTimestamps($survey);
        return view('ajax.question', ['question' => $question]);
    }

    private function saveChoices($type, $question, $choices){
        if($type->has_choices){
            foreach ($choices as $label){
                $choice = new QuestionChoice();
                $choice->label = $label;
                $choice->question()->associate($question);
                $choice->save();
            }
        }
    }

    public function manipulateSurvey($id, Request $request){
        $survey = Survey::find($id);
        switch ($request->action){
            case 'add_page':
                $page = new SurveyPage();
                DB::transaction(function () use($survey, $request, $page){
                    //SORT PAGE NUMBERS
                    $newPage =  $request->page_no + 1;
                    SurveyPage::where('survey_id', $survey->id)
                                ->where('page_no', '>=', $newPage)
                                ->increment('page_no');
                    $page->page_no = $newPage;
                    $page->survey()->associate($survey);
                    $page->save();
                });

               /* return view('ajax.page', [
                    'page' => $page,
                    'survey' => $survey,
                    'question_types' => $this->getQuestionTypes()
                ]);*/
               return json_encode(array("id" => $page->id));
                break;
            case 'edit_page_title':
                SurveyPage::find($request->page_id)
                    ->update([
                        'page_title' => $request->page_title,
                        'page_description' => $request->page_description,
                    ]);
                break;
            case 'edit_survey_title':
                $survey->update([
                    'survey_title' => $request->survey_title,
                ]);
                break;
            case 'delete_question':
                Question::destroy($request->question_id);
                return 'earl is real';
                break;
            case 'move_question':
                return DB::transaction(function () use ($request, $survey){
                    $newOrder = 1;
                    $newPage = $request->target_page_id;

                    if($request->target_id != null){
                        $newOrder = $this->adjustQuestion($request->position, $request->target_id);
                    }

                    $question = Question::find($request->question_id);
                    $question->surveyPage->questions()
                        ->where('order_no', '>', $question->order_no)
                        ->decrement('order_no');

                    $question->update([
                        'survey_page_id' => $newPage,
                        'order_no' => $newOrder
                    ]);

                    return $question->id;
                });
                break;
            case 'copy_question':
                return DB::transaction(function () use ($request, $survey) {
                    $newOrder = 1;
                    if($request->target_id != null){
                        $newOrder = $this->adjustQuestion($request->position, $request->target_id);
                    }

                    $question = Question::find($request->question_id);
                    $newQuestion = $question->replicate();
                    $newQuestion->survey_page_id = $request->target_page_id;
                    $newQuestion->order_no = $newOrder;
                    $newQuestion->save();
                    if($question->questionType->has_choices){
                        $this->replicateChoices($question, $newQuestion);
                    }
                    if($question->questionType->type == "Rating Scale"){
                        $this->replicateOption($question, $newQuestion);
                    }
                    return view('ajax.question', ['question' => $newQuestion]);
                });
                break;
            case 'delete_page':
                DB::transaction(function () use ($request, $survey) {
                    SurveyPage::destroy($request->page_id);
                    SurveyPage::where('survey_id', '=', $survey->id)
                        ->where('page_no', '>', $request->page_no)
                        ->decrement('page_no');
                });
                return 'page deleted';
                break;
            case 'copy_page':
                return DB::transaction(function () use ($request, $survey) {
                    $page = SurveyPage::find($request->page_id);
                    $targetPage = SurveyPage::find($request->target_id);

                    if($request->position == "below"){
                        $sign = '>';
                        $additional = 1;
                    }else{ //above
                        $sign = '>=';
                        $additional = 0;
                    }

                    SurveyPage::where('survey_id', $survey->id)
                        ->where('page_no', $sign, $targetPage->page_no)
                        ->increment('page_no', 1);

                    $newPage = $page->replicate();
                    $newPage->page_no = $targetPage->page_no + $additional;
                    $newPage->save();

                    $this->replicateQuestions($page, $newPage);

                    return $newPage->id;
                });
                break;
            case 'move_page':
                return DB::transaction(function () use ($request, $survey) {
                    $page = SurveyPage::find($request->page_id);
                    $targetPage = SurveyPage::find($request->target_id);
//                    $less = 0;
//                    $increment = 0;

                    if($page->page_no < $targetPage->page_no){
                        $firstSign = '>';
                        $increment = -1;
                        if($request->position == "below"){
                            $less = 0;
                            $secondSign = '<=';
                        }else{ //above
                            $less = -1;
                            $secondSign = '<';
                        }
                    }else if($page->page_no >= $targetPage->page_no){
                        $firstSign = '<';
                        $increment = 1;
                        if($request->position == "below"){
                            $less = 1;
                            $secondSign = '>';
                        }else{ //above
                            $less = 0;
                            $secondSign = '>=';
                        }
                    }

                    SurveyPage::where('survey_id', $survey->id)
                        ->where('page_no', $firstSign, $page->page_no)
                        ->where('page_no', $secondSign, $targetPage->page_no)
                        ->increment('page_no', $increment);


                    $page->update(['page_no' => $targetPage->page_no + $less]);
                    return $page->id;
                });
                break;
            default:
                //
        }
//        $this->updateSurveyTimestamps($survey);
    }

    private function adjustQuestion($position, $target_id){
        $sign = ($position == "above") ? '>=' : '>';
        $targetQuestion = Question::find($target_id);
        $newOrder = $targetQuestion->order_no;
        if($position == "below"){
            $newOrder += 1;
        }
        $targetQuestion->surveyPage->questions()
            ->where('order_no', $sign, $targetQuestion->order_no)
            ->increment('order_no');
        return $newOrder;
    }

    private function replicateQuestions(SurveyPage $page, SurveyPage $newPage){
        foreach($page->questions as $question){
            $newQuestion = $question->replicate();
            $newQuestion->survey_page_id = $newPage->id;
            $newQuestion->save();
            if($question->questionType->type == "Rating Scale"){
                $newOption = $question->option->replicate();
                $newOption->question_id = $newQuestion->id;
                $newOption->save();
            }
            $this->replicateChoices($question, $newQuestion);
        }
    }

    private function replicateChoices(Question $question, Question $newQuestion){
        foreach($question->choices as $choice){
            $newChoice = $choice->replicate();
            $newChoice->question_id = $newQuestion->id;
            $newChoice->save();
        }
    }

    private function replicateOption(Question $question, Question $newQuestion){
        $newOption = $question->option->replicate();
        $newOption->question_id = $newQuestion->id;
        $newOption->save();
    }

    public function show($id)
    {
        $survey = Survey::find($id);
        return view('survey.edit', ['survey' => $survey, 'adminMode' => false]);
    }

    public function view($id)
    {
        $survey = Survey::find($id);
        return view('survey.view', ['survey' => $survey]);
    }

    public function update(Request $request, $id)
    {
        Survey::find($id)->option()->update([
            'open' => $request->open,
            'closed_message' => $request->closed_message
        ]);

        return "ok";
    }

    public function destroy($id)
    {
        $isTemplate = Survey::find($id)->is_template;
        Survey::destroy($id);

        if($isTemplate){
            return redirect('admin/templates');
        }
        return redirect('mysurveys');
    }

    public function publish($id)
    {
        $survey = Survey::find($id);
        DB::transaction(function () use ($id, $survey) {
            $survey->update([
                'published' => true,
            ]);
            SurveyOption::create([
                'survey_id' => $id,
            ]);
        });

        if($survey->is_template){
            return redirect('admin/templates');
        }else{
            return redirect('/share/'.$id);
        }
    }

    public function share($id)
    {
        $survey = Survey::find($id);
        if($survey->published)
            return view('survey.share', ['survey' => $survey]);
        else
            return view('misc.publish-first', ['survey' => $survey]);
    }

    public function summary($id)
    {
        $survey = Survey::find($id);
        return view('survey.summary', [
            'survey' => $survey,
            'adminMode' => false,
            'option' => $survey->option,
        ]);
    }

    public function changeOptions(Request $request, $id)
    {
        Survey::find($id)->option->update([
            'response_message' => $request->response_message,
            'multiple_responses' => $request->multiple_responses,
            'target_responses' => empty($request->target_responses) ? null : $request->target_responses,
            'date_close' => empty($request->date_close) ? null : Carbon::parse($request->date_close)->toDateString(),
        ]);

        return "ok";
    }

    private function getSurveyCategories(){
        return SurveyCategory::where('category', '!=', 'Other')->orderBy('category','asc')->get();
    }

    private function getQuestionTypes(){
        return QuestionType::all();
    }

    private function updateSurveyTimestamps(Survey $survey){
        $survey->update([
            'updated_at' => Carbon::now(),
        ]);
    }

    public function settings($id){
        return view('survey.settings', ['survey' => Survey::findOrFail($id)]);
    }

}
