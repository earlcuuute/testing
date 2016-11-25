@extends($adminMode ? 'layouts.admin' : 'layouts.app-with-sidebar')

@if($adminMode)
@section('content-header')
    <section class="content-header">
        <h1>
            Edit Template
            <small>Control panel</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
            <li><a href="{{ url('admin/templates') }}">Templates</a></li>
            <li class="active">Create</li>
        </ol>


    </section>

    @if(!$survey->published)
        <section class="content-header">
            <div class="pull-right">
                <form method="POST" action="{{ url('/create/'.$survey->id) }}" class="publish-form">
                    {{ csrf_field() }}
                    {{ method_field('PATCH') }}
                    <button type="button" id="publish-survey" class="btn btn-lg btn-primary"><i
                                class="fa fa-btn fa-share"></i>Publish Template
                    </button>
                </form>
            </div>
        </section>
    @endif

@endsection

@else
@section('content-header')
    <section class="content-header">
        <h1><a href="{{ url('survey/'.$survey->id) }}">{{ $survey->survey_title }}</a></h1>
        <ol class="breadcrumb">
            <li><a href="{{ url('/') }}"><i class="fa fa-dashboard"></i> Home</a></li>
            <li><a href="{{ url('mysurveys') }}">My Surveys</a></li>
            <li><a href="{{ url('survey/'.$survey->id) }}">{{ $survey->survey_title }}</a></li>
            <li class="active">Edit</li>
        </ol>
    </section>


@endsection

{{--@section('content-header')--}}
{{--@endsection--}}
@endif

@section('style')
    @if($survey->published)
        <style>
            .editable {
                cursor: default;
            }
        </style>
    @endif
@endsection

@section('content')


    @if(!$survey->published)
        <div class="pull-right">
            <form method="POST" action="{{ url('/create/'.$survey->id) }}" class="publish-form">
                {{ csrf_field() }}
                {{ method_field('PATCH') }}
                <button type="button" id="publish-survey" class="btn btn-lg btn-primary"><i
                            class="fa fa-btn fa-share"></i>Publish Survey
                </button>
            </form>
        </div>
    @endif

    @include('modals.surveyTitle')

    @include('modals.pageTitle')

    @include('modals.question')

    @include('modals.moveCopy')

    <input type="hidden" id="selected-page-id" value="0">



    @if(!$adminMode)
        <div class="">
            @endif
            <?php $questionNo = 1; ?>
            @foreach($survey->pages()->orderBy('page_no')->get() as $page)
                <div id="page{{ $page->id }}" class="page-row" data-page-number="{{ $page->page_no }}"
                     data-id="{{ $page->id }}" data-title="{{ $page->page_title }}"
                     data-description="{{ $page->page_description }}">
                    <h4 class="survey-page-header">
                <span class="page-no">
                  Page {{ $page->page_no }}
                </span>
                        @if(!$survey->published)
                            <div class="btn-group">
                                <button type="button" class="btn btn-facebook btn-sm dropdown-toggle"
                                        data-toggle="dropdown" aria-expanded="false">
                                    <i class="fa fa-btn fa-wrench"></i>Actions
                                    <span class="caret"></span>
                                </button>
                                <ul class="dropdown-menu page-actions">
                                    <li><a data-toggle="modal" data-target="#move-copy-modal" data-action="move_page"
                                           class="move-page editable"><i class="fa fa-btn fa-cut"></i>Move Page</a></li>
                                    <li><a data-toggle="modal" data-target="#move-copy-modal" data-action="copy_page"
                                           class="replicate-page editable"><i class="fa fa-btn fa-copy"></i>Replicate
                                            Page</a></li>
                                    <li><a class="delete-page editable"><i class="fa fa-btn fa-eraser"></i>Delete
                                            Page</a></li>
                                </ul>
                            </div>
                        @endif
                    </h4>
                    <div class="panel panel-primary">

                        <div class="panel-heading editable survey-title" data-toggle="modal"
                             data-target="#survey-title-modal" value="{{ $survey->survey_title }}">
                            <h3 class="panel-title">{{ $survey->survey_title }}</h3>
                        </div>

                        <div class="panel-footer edit-page-title editable" data-toggle="modal"
                             data-target="#page-title-modal" data-identifier="title">
                            @if($page->page_title == "" || $page->page_title == null)
                                <span class="glyphicon glyphicon-plus"></span>
                                Add Page Title
                            @else
                                {{ $page->page_title }}
                            @endif
                        </div>

                        <div class="panel-body">
                            <div class="page-description edit-page-description editable {{ $survey->published ? '' : 'hoverable' }}"
                                 data-toggle="modal" data-target="#page-title-modal" data-identifier="description">
                                @if($page->page_description != nullOrEmptyString())
                                    <p>{{ $page->page_description }}</p>
                                @endif
                            </div>

                            <div class="question-container">
                                {{--AJAX WILL APPEND COMPONENTS HERE--}}
                                @foreach($page->questions()->orderBy('order_no')->get() as $question)
                                    <div class="question-row-container">

                                        @if(!$survey->published)
                                            <div class="question-row-tools editable" data-toggle="modal"
                                                 data-target="#add-question-modal">
                                                <div class="col-xs-offset-7 col-xs-5">
                                                    <div class="question-actions">
                                                        {{--<button type="button" data-action="move_question" class="btn btn-info move-question"><i class="fa fa-btn fa-pencil-square-o"></i>Edit</button>--}}
                                                        <button type="button" data-action="move_question"
                                                                class="btn btn-facebook move-question"><i
                                                                    class="fa fa-btn fa-cut"></i>Move
                                                        </button>
                                                        <button type="button" data-action="copy_question"
                                                                class="btn btn-primary copy-question"><i
                                                                    class="fa fa-btn fa-copy"></i>Replicate
                                                        </button>
                                                        <button type="button" class="btn btn-danger delete-question"><i
                                                                    class="fa fa-btn fa-eraser"></i>Delete
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif

                                        <div id="question{{ $question->id }}" class="row question-row"
                                             data-max-rating="{{ $question->option == null ? 3 : $question->option->max_rating }}"
                                             data-question-id="{{ $question->id }}"
                                             data-question-number="{{ $questionNo }}"
                                             data-question-type="{{ $question->questionType->id }}"
                                             data-is-mandatory="{{ $question->is_mandatory }}"
                                             data-has-choices="{{ $question->questionType->has_choices }}">
                                            <div class="col-xs-8 height-adjuster">
                                                <div class="form-group">
                                                    <label>
                                                        <h3>
                                                            <span class="question-number">{{ $questionNo++ }}</span>
                                                            <span class="question-dot">.</span>
                                                            <span class="question-title">{{ $question->question_title }}</span>
                                                        </h3>
                                                    </label>

                                                    @if($question->questionType->type == "Multiple Choice")
                                                        @foreach($question->choices()->get() as $choice)
                                                            <div class="radio choice-row"
                                                                 data-choice-id="{{ $choice->id }}">
                                                                <label class="choice-label"><input type="radio"
                                                                                                   name="{{ $question->id }}"> {{ $choice->label }}
                                                                </label>
                                                            </div>
                                                        @endforeach
                                                    @elseif($question->questionType()->first()->type == "Dropdown")
                                                        <select class="form-control">
                                                            @foreach($question->choices()->get() as $choice)
                                                                <option class="choice-row choice-label"
                                                                        data-choice-id="{{ $choice->id }}">{{ $choice->label }}</option>
                                                            @endforeach
                                                        </select>
                                                    @elseif($question->questionType()->first()->type == "Checkbox")
                                                        @foreach($question->choices()->get() as $choice)
                                                            <div class="checkbox choice-row"
                                                                 data-choice-id="{{ $choice->id }}">
                                                                <label class="choice-label"><input
                                                                            type="checkbox"> {{ $choice->label }}
                                                                </label>
                                                            </div>
                                                        @endforeach
                                                    @elseif($question->questionType()->first()->type == "Rating Scale")
                                                        <select class="rating-scale">
                                                            <option value="-1"></option>
                                                            @for($i=1; $i<=$question->option->max_rating; $i++)
                                                                <option value="{{ $i }}">{{ $i }}</option>
                                                            @endfor
                                                        </select>
                                                    @elseif($question->questionType->type == "Textbox")
                                                        <div class="form-group">
                                                            <input type="text" class="form-control">
                                                        </div>
                                                    @elseif($question->questionType->type == "Text Area")
                                                        <div class="form-group">
                                                            <textarea cols="30" rows="2"
                                                                      class="form-control"></textarea>
                                                        </div>
                                                    @endif

                                                </div>
                                            </div>
                                        </div>

                                    </div>

                                @endforeach
                            </div>

                            @if(!$survey->published)
                                <div class="row" style="margin-top: 15px">
                                    <div class="btn-group col-xs-offset-4">
                                        <button type="button" class="btn btn-primary add-question"
                                                data-question-number="{{ $questionNo }}"
                                                data-page="{{ $page->page_no }}" data-toggle="modal"
                                                data-target="#add-question-modal">
                                            <i class="fa fa-plus-circle"></i> Add new Question
                                        </button>
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-primary dropdown-toggle"
                                                    data-toggle="dropdown" data-question-number="{{ $questionNo }}">
                                                <span class="fa fa-caret-down"></span></button>
                                            <ul class="dropdown-menu">
                                                @foreach($question_types as $question_type)
                                                    <li class="type-option editable"><a data-toggle="modal"
                                                                                        data-target="#add-question-modal"
                                                                                        data-question-number="{{ $questionNo }}"
                                                                                        data-question-type="{{ $question_type->id }}"
                                                                                        has-choices="{{ $question_type->has_choices }}">{{ $question_type->type }}</a>
                                                    </li>
                                                @endforeach

                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            @endif


                        </div>
                    </div>

                    @if(!$survey->published)
                        <div class="form-group">
                            <button class="btn btn-lg btn-default add-page col-xs-12" type="button"><span
                                        class="fa fa-plus-square-o"></span> Add Page
                            </button>
                        </div>
                    @endif

                </div>
            @endforeach
            @if(!$adminMode)
        </div>
    @endif
@endsection

@section('scripts')

    <script>
        var published = {{ $survey->published }};
        var surveyId = {{ $survey->id }};
        var createUrl = '{{ url('create/' .$survey->id) }}';

        //        console.log('earl is real');
        ratingScale();
    </script>

    <!-- Survey Scripts -->
    <script src="{{ asset('public/js/survey.js') }}"></script>
    <script src="{{ asset('public/js/page-functions.js') }}"></script>
    <script src="{{ asset('public/js/question-functions.js') }}"></script>
    <script src="{{ asset('public/js/loader.js') }}"></script>


@endsection