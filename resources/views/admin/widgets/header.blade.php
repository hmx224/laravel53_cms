<header class="main-header">
    <!-- Logo -->
    <a href="/admin/dashboard" class="logo">
        <!-- mini logo for sidebar mini 50x50 pixels -->
        <span class="logo-mini"><b>CMS</b></span>
        <!-- logo for regular state and mobile devices -->
        <span class="logo-lg"><b>内容</b>管理系统</span>
    </a>
    <!-- Header Navbar: style can be found in header.less -->
    <nav class="navbar navbar-static-top" role="navigation">
        <!-- Sidebar toggle button-->
        <a href="#" class="sidebar-toggle" data-toggle="offcanvas" role="button">
            <span class="sr-only">Toggle navigation</span>
        </a>
        <div class="navbar-custom-menu">
            <ul class="nav navbar-nav">
                <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown"><i class="fa fa-sitemap"></i> {{ auth()->user()->site->title }}<span class="caret"></span></a>
                    <ul class="dropdown-menu" role="menu">
                        @foreach(auth()->user()->sites as $site)
                        <li><a href="javascript:void(0);" onclick="setSite({{$site->id}})">{{ $site->title }}</a></li>
                        @endforeach
                    </ul>
                </li>

            <!-- TODO 待完善@ include('admin.widgets.message') -->

            <!-- User Account: style can be found in dropdown.less -->
                <li class="dropdown user user-menu">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                        <img src="{{ url('images/avatar_user.png') }}" class="user-image" alt="User Image">
                        <span class="hidden-xs">{{ Auth::user()->name }}</span>
                    </a>
                    <ul class="dropdown-menu">
                        <!-- User image -->
                        <li class="user-header">
                            <img src="{{ url('images/avatar_user.png') }}" class="img-circle" alt="User Image">
                            <p>
                                {{ Auth::user()->name }}
                            </p>
                        </li>
                        <!-- Menu Footer-->
                        <li class="user-footer">
                            <div class="pull-left">
                                <a href="profiles" class="btn btn-default btn-flat">个人资料</a>
                            </div>
                            <div class="pull-right">
                                <a href="/admin/logout" class="btn btn-default btn-flat">注销</a>
                            </div>
                        </li>
                    </ul>
                </li>
                <!-- Control Sidebar Toggle Button -->
                <li>
                    <a href="#" data-toggle="control-sidebar"><i class="fa fa-gears"></i></a>
                </li>
            </ul>
        </div>
    </nav>
</header>
<script type="text/javascript">
    function setSite(value) {
        document.cookie = "site_id" + "="+ escape (value);
        location.reload();
    }
</script>
