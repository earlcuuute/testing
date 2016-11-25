<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">

    <title>eSurvey</title>

    <link rel="icon" href="{{ asset('public/images/icon.png') }}">

    <!-- Fonts -->
    <!-- Font Awesome 4.6.3 -->
    <link rel="stylesheet" href="{{ asset('public/plugins/font-awesome-4.6.3/css/font-awesome.min.css') }}">
    <!-- Lato Font -->
{{--<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Lato:100,300,400,700">--}}

<!-- Miscs -->
    <!-- Ionicons -->
    <link rel="stylesheet" href="{{ asset('public/plugins/ionicons-2.0.1/css/ionicons.min.css') }}">
    <!-- DataTables -->
    <link rel="stylesheet" href="{{ asset('public/plugins/datatables/dataTables.bootstrap.css') }}">
    <!-- jQuery Toast -->
    <link rel="stylesheet" href="{{ asset('public/plugins/jquery-toast-plugin/jquery.toast.min.css') }}">
    <!-- jQuery Toast -->
    <link rel="stylesheet" href="{{ asset('public/plugins/toast-plugin/jquery.toastmessage.css') }}">
    <!-- icheck -->
    <link rel="stylesheet" href="{{ asset('public/plugins/iCheck/all.css') }}">
    <!-- DateRange Picker -->
    <link rel="stylesheet" href="{{ asset('public/plugins/daterangepicker/daterangepicker.css') }}">
    <!-- Bar rating -->
    <link rel="stylesheet" href="{{ asset('public/plugins/jquery-bar-rating/themes/bars-square.css') }}">
    <!-- Star rating -->
    <link rel="stylesheet" href="{{ asset('public/plugins/jquery-bar-rating/themes/fontawesome-stars.css') }}">

    <!-- Styles -->
    <!-- Bootstrap 3.3.7-->
    <link rel="stylesheet" href="{{ asset('public/plugins/bootstrap-3.3.7/css/bootstrap.min.css') }}">
    <!-- Theme style -->


    <link rel="stylesheet" href="{{ asset('public/plugins/AdminLTE-2.3.5/dist/css/AdminLTE.min.css') }}">

    {{--<link rel="stylesheet" href="{{ asset('public/css/sidebar.css') }}">--}}

    <link rel="stylesheet" href="{{ asset('public/css/app-sidebar.css') }}">

    <!-- AdminLTE Skins. Choose a skin from the css/skins
         folder instead of downloading all of them to reduce the load. -->
    <link rel="stylesheet" href="{{ asset('public/plugins/AdminLTE-2.3.5/dist/css/skins/skin-esurvey.css') }}">

    {{-- <link href="{{ elixir('css/app.css') }}" rel="stylesheet"> --}}

<!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>

    <!-- Main CSS -->
    <link rel="stylesheet" href="{{ asset('public/css/main.css') }}">

    @yield('style')
</head>
<body id="app-layout" class="hold-transition skin-esurvey layout-top-nav">

<div class="wrapper">

    <header class="main-header top-header">
        <nav class="navbar navbar-default navbar-static-top">
            <div class="container">

                <div class="navbar-header">
                    <button type="button" class="navbar-toggle collapsed" data-toggle="collapse"
                            data-target="#navbar-collapse">
                        <i class="fa fa-bars"></i>
                    </button>
                </div>

                <!-- Left Side Of Navbar -->
                <div class="collapse navbar-collapse pull-left" id="navbar-collapse">
                    <ul class="nav navbar-nav">
                    @if (Auth::user())

                        <!-- Create Survey, Display in all pages -->
                            <li><a href="{{ url('/create') }}" id="create-survey" class="btn-facebook"><i
                                            class="fa fa-plus"></i> Create Survey</a></li>
                            <li><a href="{{ url('mysurveys') }}"><i class="fa fa-edit"></i> My Surveys</a></li>
                            <li><a href="{{ url('templates') }}"><i class="fa fa-list-alt"></i> Templates</a></li>
                            <li class="dropdown">
                                <a href="#" class="dropdown-toggle" data-toggle="dropdown"><i
                                            class="fa fa-area-chart"></i> Reports <span class="caret"></span></a>
                                <ul class="dropdown-menu" role="menu">
                                    <li><a href="#">Summary</a></li>
                                    <li><a href="#">User Responses</a></li>
                                    <li><a href="#">Sources</a></li>
                                </ul>
                            </li>
                            @if(Auth::user()->role->title != "User")
                                <li><a href="{{ url('admin') }}"><i class="fa fa-user"></i> Admin Mode</a></li>
                            @endif
                        @endif
                    </ul>
                </div>

                <!-- Right Side Of Navbar -->
                <div class="navbar-custom-menu">
                    <ul class="nav navbar-nav">

                        <!-- Authentication Links -->
                        @if (Auth::guest())
                            <li><a href="{{ url('/login') }}">Login</a></li>
                            <li><a href="{{ url('/register') }}">Register</a></li>
                        @else

                        <!-- User Account Menu -->
                            @include('common.user-menu')
                        @endif
                    </ul>
                </div>
            </div>
        </nav>
    </header>


@yield('sidebar')

<!-- content goes here -->
    <div class="content-wrapper with-sidebar">

        <div class="container no-padding">
            {{--<section class="content-header">--}}
            {{--<h1>--}}
            {{--@yield('header-text')--}}
            {{--</h1>--}}
            {{--<ol class="breadcrumb">--}}
            {{--@yield('breadcrumb')--}}
            {{--</ol>--}}
            {{--</section>--}}


            <div class="main-page">
                <div class="sidebar">
                    <div class="text-center" style="margin-top:10px; margin-bottom: 10px;">
                        <a class="text-center" href="{{ url('mysurveys') }}">
                            <img src="{{ asset('public/images/side-logo.png') }}" alt="logo" style="height:90px;">
                        </a>
                    </div>
                    <!-- sidebar menu: : style can be found in sidebar.less -->
                    <ul class="sidebar-menu">
                        <li class="header">SURVEY NAVIGATION</li>
                        <li><a href="{{ url('survey/'.$survey->id) }}"><i class="fa fa-dashboard"></i> Dashboard</a></li>
                        @if($survey->published)
                        <li><a href=""><i class="fa fa-eye"></i> Preview</a></li>
                        @else
                            <li><a href="{{ url('create/'.$survey->id) }}"><i class="fa fa-edit"></i> Edit</a></li>
                            @endif
                        <li><a href="{{ url('share/'.$survey->id) }}"><i class="fa fa-share-alt"></i> Share</a></li>
                        <li><a href="{{ url('settings/'.$survey->id) }}"><i class="fa fa-wrench"></i> Settings</a></li>
                        <li class="treeview">
                            <a href="">
                                <i class="fa fa-line-chart"></i>
                                <span>Analyze</span>
                                <span class="pull-right-container">
                             <i class="fa fa-angle-left pull-right"></i>
                        </span>
                            </a>
                            <ul class="treeview-menu">
                                <li><a href="{{ url('analyze/'.$survey->id) }}"><i class="fa fa-pie-chart"></i> Summary</a></li>
                                <li><a href="{{ url('analyze/'.$survey->id.'/user') }}"><i class="fa fa-users"></i> Individual Responses</a></li>
                            </ul>
                        </li>
                        <li><a href="{{ url('analyze/'.$survey->id.'/result.pdf') }}" target="_blank"><i class="fa fa-file-pdf-o"></i> Quick Report</a></li>
                        <li class="treeview">
                            <a href="">
                                <i class="fa fa-gears"></i>
                                <span>Other</span>
                                <span class="pull-right-container">
                             <i class="fa fa-angle-left pull-right"></i>
                        </span>
                            </a>
                            <ul class="treeview-menu">
                                <li><a href=""><i class="fa fa-copy"></i> Duplicate Survey</a></li>
                                <li><a href=""><i class="fa fa-remove"></i> Delete</a></li>
                            </ul>
                        </li>
                    </ul>
                </div>
                <div class="main-content">

                    @yield('content-header')

                    <div id="page-loader" class="text-center">
                        <i id="loading-spinner" class="fa fa-spinner fa-pulse fa-5x fa-fw"></i>
                    </div>

                    <div id="page-content" class="content">
                        @yield('content')
                    </div>
                </div>
            </div>

        </div>
    </div>

    <footer class="main-footer">
        <div class="container">
            <div class="pull-right hidden-xs">
                <b>Version</b> 0.1.2
            </div>
            <strong>Copyright &copy; 2016-2017 <a href="#">Fantastic Four</a>.</strong> All rights
            reserved.
        </div>
        <!-- /.container -->
    </footer>

</div>
<!-- ./wrapper -->

<!-- jQuery 2.2.3 -->
<script src="{{ asset('public/plugins/jQuery/jquery-2.2.3.min.js') }}"></script>
<!-- Bootstrap 3.3.6 -->
<script src="{{ asset('public/plugins/bootstrap-3.3.7/js/bootstrap.min.js') }}"></script>
<!-- SlimScroll -->
<script src="{{ asset('public/plugins/slimScroll/jquery.slimscroll.min.js') }}"></script>
<!-- FastClick -->
<script src="{{ asset('public/plugins/fastclick/fastclick.js') }}"></script>
<!-- AdminLTE App -->
<script src="{{ asset('public/plugins/AdminLTE-2.3.5/dist/js/app.min.js') }}"></script>
<!-- AdminLTE for demo purposes -->
<script src="{{ asset('public/plugins/AdminLTE-2.3.5/dist/js/demo.js') }}"></script>

<!-- Miscellaneous -->
<!-- To Title Case -->
<script src="{{ asset('public/plugins/to-title-case/to-title-case.js') }}"></script>
<!-- BootBox 4.4.0-->
<script src="{{ asset('public/plugins/bootbox/bootbox.min.js') }}"></script>
<!-- DataTables -->
<script src="{{ asset('public/plugins/datatables/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('public/plugins/datatables/dataTables.bootstrap.min.js') }}"></script>
<!-- jQuery Toast -->
<script src="{{ asset('public/plugins/jquery-toast-plugin/jquery.toast.min.js') }}"></script>
<!-- jQuery Toast Message -->
<script src="{{ asset('public/plugins/toast-plugin/jquery.toastmessage.js') }}"></script>
<!-- icheck -->
<script src="{{ asset('public/plugins/iCheck/icheck.min.js') }}"></script>
<!-- Moment -->
<script src="{{ asset('public/plugins/daterangepicker/moment.min.js') }}"></script>
<!-- DateRange Picker -->
<script src="{{ asset('public/plugins/daterangepicker/daterangepicker.js') }}"></script>
<!-- Barrating -->
<script src="{{ asset('public/plugins/jquery-bar-rating/jquery.barrating.min.js') }}"></script>

<!-- GLOBAL JS -->
<script src="{{ asset('public/js/global.js') }}"></script>


@yield('scripts')


<meta name="csrf-token" content="{{ csrf_token() }}">

</body>
</html>