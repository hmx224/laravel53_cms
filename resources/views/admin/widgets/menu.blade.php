<!-- Left side column. contains the logo and sidebar -->
<aside class="main-sidebar">
    <!-- sidebar: style can be found in sidebar.less -->
    <section class="sidebar">
        <!-- sidebar menu: : style can be found in sidebar.less -->
        <ul class="sidebar-menu">
            <li class="header">菜单导航</li>
            {!! \App\Helpers\HtmlBuilder::menuTree(auth()->user()->site->menus()->where('parent_id', 0)->orderBy('sort')->get()) !!}
            <li class="treeview">
                <a href="#">
                    <i class="fa fa-sitemap"></i>
                    <span class="menu-item-top">站点管理</span>
                    <i class="fa fa-angle-left pull-right"></i>
                </a>
                <ul class="treeview-menu">
                    @can('@site')
                        <li><a href="/admin/sites"><i class="fa fa-gears"></i> 站点设置</a></li>
                    @endcan
                    @can('@category')
                        <li><a href="/admin/categories"><i class="fa fa-columns"></i> 栏目管理</a></li>
                    @endcan
                    @can('@theme')
                        <li><a href="/admin/themes"><i class="fa fa-paint-brush"></i> 主题管理</a></li>
                    @endcan
                </ul>
            </li>
            <li class="treeview">
                <a href="#">
                    <i class="fa fa-cog"></i>
                    <span class="menu-item-top">系统管理</span>
                    <i class="fa fa-angle-left pull-right"></i>
                </a>
                <ul class="treeview-menu">
                    @can('@option')
                        <li><a href="/admin/options"><i class="fa fa-cog"></i> 系统设置</a></li>
                    @endcan
                    @can('@dictionary')
                        <li><a href="/admin/dictionaries"><i class="fa fa-book"></i> 字典设置</a></li>
                    @endcan
                    @can('@app')
                        <li><a href="/admin/apps"><i class="fa fa-android"></i> 应用管理</a></li>
                    @endcan
                    @can('@module')
                        <li><a href="/admin/modules"><i class="fa fa-cubes"></i> 模块管理</a></li>
                    @endcan
                    @can('@menu')
                        <li><a href="/admin/menus"><i class="fa fa-bars"></i> 菜单管理</a></li>
                    @endcan
                    @can('@role')
                        <li><a href="/admin/roles"><i class="fa fa-street-view"></i> 角色管理</a></li>
                    @endcan
                    @can('@user')
                        <li><a href="/admin/users"><i class="fa fa-user"></i> 用户管理</a></li>
                    @endcan
                </ul>
            </li>
        </ul>
    </section>
    <!-- /.sidebar -->
</aside>

<script>
    $(document).ready(function () {
        var url = window.location.pathname;
        url = url.replace(/\/[0-9]*\/edit/, ''); // articles/1/edit
        url = url.replace(/\b\/create[\/0-9]*\b/, ''); // categories/create/1
        url = url.replace(/\b\/create\?[\s\S]*\b/, ''); // articles/create?category_id=1
        $('ul.treeview-menu>li').find('a[href="' + url + '"]').closest('li').addClass('active');  //二级链接高亮
        $('ul.treeview-menu>li').find('a[href="' + url + '"]').closest('li.treeview').addClass('active');  //一级链接高亮
        $('ul.treeview-menu>li').find('a[href="' + url + '"]').parent().addClass('active'); //单独一级高亮
    });
</script>
