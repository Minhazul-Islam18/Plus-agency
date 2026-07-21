@php
    $default = \App\Language::where('is_default', 1)->first();
    $admin = Auth::guard('admin')->user();
    if (!empty($admin->role)) {
        $permissions = $admin->role->permissions;
        $permissions = json_decode($permissions, true);
    }

    $data = \App\BasicExtra::first();
@endphp

<div class="sidebar sidebar-style-2" data-background-color="dark2">
    <div class="sidebar-wrapper scrollbar scrollbar-inner">
        <div class="sidebar-content">
            <div class="user">
                <div class="avatar-sm float-left mr-2">
                    @if (!empty(Auth::guard('admin')->user()->image))
                        <img src="{{ asset('assets/admin/img/propics/' . Auth::guard('admin')->user()->image) }}"
                            alt="..." class="avatar-img rounded">
                    @else
                        <img src="{{ asset('assets/admin/img/propics/blank_user.jpg') }}" alt="..."
                            class="avatar-img rounded">
                    @endif
                </div>
                <div class="info">
                    <a data-toggle="collapse" href="#collapseExample" aria-expanded="true">
                        <span>
                            {{ Auth::guard('admin')->user()->first_name }}
                            @if (empty(Auth::guard('admin')->user()->role))
                                <span class="user-level">Owner</span>
                            @else
                                <span class="user-level">{{ Auth::guard('admin')->user()->role->name }}</span>
                            @endif
                            <span class="caret"></span>
                        </span>
                    </a>
                    <div class="clearfix"></div>
                    <div class="collapse in" id="collapseExample">
                        <ul class="nav">
                            <li>
                                <a href="{{ route('admin.editProfile') }}">
                                    <span class="link-collapse">Edit Profile</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('admin.changePass') }}">
                                    <span class="link-collapse">Change Password</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('admin.logout') }}">
                                    <span class="link-collapse">Logout</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <ul class="nav nav-primary mt-0">
                <div class="row mb-2">
                    <div class="col-12">
                        <form action="">
                            <div class="form-group py-0">
                                <input name="term" type="text" class="form-control sidebar-search" value=""
                                    placeholder="Search Menu Here...">
                            </div>
                        </form>
                    </div>
                </div>

                @if (empty($admin->role) || (!empty($permissions) && in_array('Dashboard', $permissions)))
                    {{-- Dashboard --}}
                    <li class="nav-item @if (request()->path() == 'admin/dashboard') active @endif">
                        <a href="{{ route('admin.dashboard') }}">
                            <i class="la flaticon-paint-palette"></i>
                            <p>Dashboard</p>
                        </a>
                    </li>
                @endif


                @if (empty($admin->role) || (!empty($permissions) && in_array('Theme & Home', $permissions)))
                    {{-- Dynamic Pages --}}
                    <li
                        class="nav-item
                @if (request()->path() == 'admin/home-settings') active
                @elseif(request()->path() == 'admin/home-page') active @endif">
                        <a data-toggle="collapse" href="#themeHome">
                            <i class="la flaticon-file"></i>
                            <p>Theme & Home
                                @if ($bex?->home_page_pagebuilder == 1)
                                    <span class="badge badge-danger p-1 sidenav-badge">Pagebuilder</span>
                                @endif
                            </p>
                            <span class="caret"></span>
                        </a>
                        <div class="collapse
                @if (request()->path() == 'admin/home-settings') show
                @elseif(request()->path() == 'admin/home-page') show @endif"
                            id="themeHome">
                            <ul class="nav nav-collapse">
                                <li class="@if (request()->path() == 'admin/home-settings') active @endif">
                                    <a href="{{ route('admin.homeSettings') }}">
                                        <span class="sub-item">Settings</span>
                                    </a>
                                </li>
                                @if ($bex?->home_page_pagebuilder == 1)
                                    <li class="@if (request()->path() == 'admin/home-page') active @endif">
                                        <a href="#" data-toggle="modal" data-target="#pbLangModal">
                                            <span class="sub-item">Home Page Content</span>
                                        </a>
                                    </li>
                                @endif
                            </ul>
                        </div>
                    </li>
                @endif


                @if (empty($admin->role) || (!empty($permissions) && in_array('Menu Builder', $permissions)))
                    {{-- Menu Builder --}}
                    <li
                        class="nav-item
        @if (request()->path() == 'admin/menu-builder') active
        @elseif(request()->path() == 'admin/megamenus') active
        @elseif(request()->path() == 'admin/megamenus/edit') active
        @elseif(request()->path() == 'admin/permalinks') active @endif">
                        <a data-toggle="collapse" href="#websiteMenu">
                            <i class="fas fa-ellipsis-v"></i>
                            <p>Website Menu Builder</p>
                            <span class="caret"></span>
                        </a>
                        <div class="collapse
        @if (request()->path() == 'admin/menu-builder') show
        @elseif(request()->path() == 'admin/megamenus') show
        @elseif(request()->path() == 'admin/permalinks') show
        @elseif(request()->path() == 'admin/megamenus/edit') show @endif"
                            id="websiteMenu">
                            <ul class="nav nav-collapse">
                                <li
                                    class="@if (request()->path() == 'admin/megamenus') active
                @elseif(request()->path() == 'admin/megamenus/edit') active @endif">
                                    <a href="{{ route('admin.megamenus') . '?language=' . $default->code }}">
                                        <span class="sub-item">Mega Menus</span>
                                    </a>
                                </li>
                                <li class="@if (request()->path() == 'admin/menu-builder') active @endif">
                                    <a href="{{ route('admin.menu_builder.index') . '?language=' . $default->code }}">
                                        <span class="sub-item">Main Menu</span>
                                    </a>
                                </li>
                                <li class="@if (request()->path() == 'admin/permalinks') active @endif">
                                    <a href="{{ route('admin.permalinks.index') }}">
                                        <span class="sub-item">Permalinks</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                @endif



                {{-- Content Management --}}
                @if (empty($admin->role) || (!empty($permissions) && in_array('Content Management', $permissions)))
                    @includeIf('admin.partials.content-management')
                @endif


                @if (empty($admin->role) || (!empty($permissions) && in_array('Pages', $permissions)))
                    {{-- Dynamic Pages --}}
                    <li
                        class="nav-item
@if (request()->path() == 'admin/page/create') active
@elseif(request()->path() == 'admin/page/settings') active
@elseif(request()->path() == 'admin/pages') active
@elseif(request()->is('admin/page/*/edit')) active @endif">
                        <a data-toggle="collapse" href="#pages">
                            <i class="la flaticon-file"></i>
                            <p>Custom Pages <span class="badge badge-danger p-1 sidenav-badge">Pagebuilder</span></p>
                            <span class="caret"></span>
                        </a>
                        <div class="collapse
@if (request()->path() == 'admin/page/create') show
@elseif(request()->path() == 'admin/page/settings') show
@elseif(request()->path() == 'admin/pages') show
@elseif(request()->is('admin/page/*/edit')) show @endif"
                            id="pages">
                            <ul class="nav nav-collapse">
                                <li class="@if (request()->path() == 'admin/page/settings') active @endif">
                                    <a href="{{ route('admin.page.settings') }}">
                                        <span class="sub-item">Settings</span>
                                    </a>
                                </li>
                                <li class="@if (request()->path() == 'admin/page/create') active @endif">
                                    <a href="{{ route('admin.page.create') . '?language=' . $default->code }}">
                                        <span class="sub-item">Create Page</span>
                                    </a>
                                </li>
                                <li class="@if (request()->path() == 'admin/pages') active @endif">
                                    <a href="{{ route('admin.page.index') . '?language=' . $default->code }}">
                                        <span class="sub-item">Pages</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                @endif


                @if (empty($admin->role) || (!empty($permissions) && in_array('Tender Management', $permissions)))
                    {{-- Tenders --}}
                    <li
                        class="nav-item
@if (request()->path() == 'admin/tender_categories') active
@elseif(request()->path() == 'admin/tender/settings') active
@elseif(request()->path() == 'admin/tender/purchase-log') active
@elseif(request()->path() == 'admin/tender/blacklist') active
@elseif(request()->path() == 'admin/tenders') active
@elseif(request()->path() == 'admin/tender/create') active
@elseif(request()->is('admin/tender/*/edit')) active
@elseif(request()->path() == 'admin/tender/enrolls/report') active @endif">
                        <a data-toggle="collapse" href="#tender">
                            <i class='fas fa-file-contract'></i>
                            <p>Tender Management</p>
                            <span class="caret"></span>
                        </a>
                        <div class="collapse
@if (request()->path() == 'admin/tender_categories') show
@elseif(request()->path() == 'admin/tender/settings') show
@elseif(request()->path() == 'admin/tender/purchase-log') show
@elseif(request()->path() == 'admin/tender/blacklist') show
@elseif(request()->path() == 'admin/tenders') show
@elseif(request()->path() == 'admin/tender/create') show
@elseif(request()->is('admin/tender/*/edit')) show
@elseif(request()->path() == 'admin/tender/enrolls/report') show @endif"
                            id="tender">
                            <ul class="nav nav-collapse">
                                <li class="@if (request()->path() == 'admin/tender/settings') active @endif">
                                    <a href="{{ route('admin.tender.settings') . '?language=' . $default->code }}">
                                        <span class="sub-item">Settings</span>
                                    </a>
                                </li>
                                <li class="@if (request()->path() == 'admin/tender_categories') active @endif">
                                    <a
                                        href="{{ route('admin.tender_category.index') . '?language=' . $default->code }}">
                                        <span class="sub-item">Category</span>
                                    </a>
                                </li>
                                <li class="@if (request()->path() == 'admin/tender/create') active @endif">
                                    <a href="{{ route('admin.tender.create') . '?language=' . $default->code }}">
                                        <span class="sub-item">Add Tender</span>
                                    </a>
                                </li>
                                <li
                                    class="@if (request()->path() == 'admin/tenders') active
        @elseif(request()->is('admin/tender/*/edit')) active @endif">
                                    <a href="{{ route('admin.tender.index') . '?language=' . $default->code }}">
                                        <span class="sub-item">All Tenders</span>
                                    </a>
                                </li>
                                <li class="@if (request()->path() == 'admin/tender/purchase-log') active @endif">
                                    <a href="{{ route('admin.tender.purchaseLog') }}">
                                        <span class="sub-item">Purchases</span>
                                    </a>
                                </li>
                                <li class="@if (request()->path() == 'admin/tender/blacklist') active @endif">
                                    <a href="{{ route('admin.tender.blacklist') }}">
                                        <span class="sub-item">Blacklist</span>
                                    </a>
                                </li>
                                <li class="@if (request()->path() == 'admin/tender/enrolls/report') active @endif">
                                    <a href="{{ route('admin.tender.enrolls.report') }}">
                                        <span class="sub-item">Report</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                @endif








                {{-- Users Management --}}
                @if (empty($admin->role) || (!empty($permissions) && in_array('Users Management', $permissions)))
                    <li
                        class="nav-item
@if (request()->routeIs('admin.register.user')) active
@elseif(request()->routeIs('register.user.view')) active
@elseif(request()->routeIs('register.user.changePass')) active

@elseif(request()->path() == 'admin/pushnotification/settings') active
@elseif(request()->path() == 'admin/pushnotification/send') active

@elseif(request()->path() == 'admin/subscribers') active
@elseif(request()->path() == 'admin/mailsubscriber') active @endif">
                        <a data-toggle="collapse" href="#usersManagement">
                            <i class="la flaticon-users"></i>
                            <p>Users Management</p>
                            <span class="caret"></span>
                        </a>
                        <div class="collapse
@if (request()->routeIs('admin.register.user')) show
@elseif(request()->routeIs('register.user.view')) show
@elseif(request()->routeIs('register.user.changePass')) show

@elseif(request()->path() == 'admin/pushnotification/settings') show
@elseif(request()->path() == 'admin/pushnotification/send') show

@elseif(request()->path() == 'admin/subscribers') show
@elseif(request()->path() == 'admin/mailsubscriber') show @endif"
                            id="usersManagement">
                            <ul class="nav nav-collapse">

                                {{-- Registered Users --}}
                                <li
                                    class="
    @if (request()->routeIs('admin.register.user')) active
    @elseif(request()->routeIs('register.user.view')) active
    @elseif(request()->routeIs('register.user.changePass')) active @endif">
                                    <a href="{{ route('admin.register.user') }}">
                                        <span class="sub-item">Registered Users</span>
                                    </a>
                                </li>

                                {{-- Push Notification --}}
                                <li
                                    class="
@if (request()->path() == 'admin/pushnotification/settings') selected
@elseif(request()->path() == 'admin/pushnotification/send') selected @endif">
                                    <a data-toggle="collapse" href="#pushNotification">
                                        <span class="sub-item">Push Notification</span>
                                        <span class="caret"></span>
                                    </a>
                                    <div class="collapse
@if (request()->path() == 'admin/pushnotification/settings') show
@elseif(request()->path() == 'admin/pushnotification/send') show @endif"
                                        id="pushNotification">
                                        <ul class="nav nav-collapse subnav">
                                            <li class="@if (request()->path() == 'admin/pushnotification/settings') active @endif">
                                                <a href="{{ route('admin.pushnotification.settings') }}">
                                                    <span class="sub-item">Settings</span>
                                                </a>
                                            </li>
                                            <li class="@if (request()->path() == 'admin/pushnotification/send') active @endif">
                                                <a href="{{ route('admin.pushnotification.send') }}">
                                                    <span class="sub-item">Send Notification</span>
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </li>

                                {{-- Subscribers --}}
                                <li
                                    class="
@if (request()->path() == 'admin/subscribers') selected
@elseif(request()->path() == 'admin/mailsubscriber') selected @endif">
                                    <a data-toggle="collapse" href="#subscribers">
                                        <span class="sub-item">Subscribers</span>
                                        <span class="caret"></span>
                                    </a>
                                    <div class="collapse
@if (request()->path() == 'admin/subscribers') show
@elseif(request()->path() == 'admin/mailsubscriber') show @endif"
                                        id="subscribers">
                                        <ul class="nav nav-collapse subnav">
                                            <li class="@if (request()->path() == 'admin/subscribers') active @endif">
                                                <a href="{{ route('admin.subscriber.index') }}">
                                                    <span class="sub-item">Subscribers</span>
                                                </a>
                                            </li>
                                            <li class="@if (request()->path() == 'admin/mailsubscriber') active @endif">
                                                <a href="{{ route('admin.mailsubscriber') }}">
                                                    <span class="sub-item">Mail to Subscribers</span>
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </li>
                @endif


                {{-- Announcement Popup --}}
                @if (empty($admin->role) || (!empty($permissions) && in_array('Announcement Popup', $permissions)))
                    <li
                        class="nav-item
@if (request()->path() == 'admin/popup/create') active
@elseif(request()->path() == 'admin/popup/types') active
@elseif(request()->is('admin/popup/*/edit')) active
@elseif(request()->path() == 'admin/popups') active @endif">
                        <a data-toggle="collapse" href="#announcementPopup">
                            <i class="fas fa-bullhorn"></i>
                            <p>Announcement Popup</p>
                            <span class="caret"></span>
                        </a>
                        <div class="collapse
@if (request()->path() == 'admin/popup/create') show
@elseif(request()->path() == 'admin/popup/types') show
@elseif(request()->path() == 'admin/popups') show
@elseif(request()->is('admin/popup/*/edit')) show @endif"
                            id="announcementPopup">
                            <ul class="nav nav-collapse">
                                <li
                                    class="@if (request()->path() == 'admin/popup/types') active
        @elseif(request()->path() == 'admin/popup/create') active @endif">
                                    <a href="{{ route('admin.popup.types') }}">
                                        <span class="sub-item">Add Popup</span>
                                    </a>
                                </li>
                                <li
                                    class="@if (request()->path() == 'admin/popups') active
        @elseif(request()->is('admin/popup/*/edit')) active @endif">
                                    <a href="{{ route('admin.popup.index') . '?language=' . $default->code }}">
                                        <span class="sub-item">Popups</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                @endif


                @if (empty($admin->role) || (!empty($permissions) && in_array('Basic Settings', $permissions)))
                    {{-- Basic Settings --}}
                    <li
                        class="nav-item
@if (request()->path() == 'admin/logo') active
@elseif(request()->path() == 'admin/file-manager') active
@elseif(request()->path() == 'admin/preloader') active
@elseif(request()->path() == 'admin/basicinfo') active
@elseif(request()->path() == 'admin/support') active
@elseif(request()->path() == 'admin/social') active
@elseif(request()->is('admin/social/*')) active
@elseif(request()->path() == 'admin/heading') active
@elseif(request()->path() == 'admin/script') active
@elseif(request()->path() == 'admin/seo') active
@elseif(request()->path() == 'admin/maintainance') active
@elseif(request()->path() == 'admin/cookie-alert') active
@elseif(request()->path() == 'admin/mail-from-admin') active
@elseif(request()->path() == 'admin/mail-to-admin') active
@elseif(request()->routeIs('admin.featuresettings')) active
@elseif(request()->path() == 'admin/email-templates') active
@elseif(request()->routeIs('admin.email.editTemplate')) active
@elseif(request()->path() == 'admin/languages') active
@elseif(request()->is('admin/language/*/edit')) active
@elseif(request()->is('admin/language/*/edit/keyword')) active
@elseif(request()->path() == 'admin/gateways') active
@elseif(request()->path() == 'admin/offline/gateways') active
@elseif(request()->path() == 'admin/backup') active
@elseif(request()->path() == 'admin/sitemap') active @endif">
                        <a data-toggle="collapse" href="#basic">
                            <i class="la flaticon-settings"></i>
                            <p>Settings</p>
                            <span class="caret"></span>
                        </a>
                        <div class="collapse
@if (request()->path() == 'admin/logo') show
@elseif(request()->path() == 'admin/file-manager') show
@elseif(request()->path() == 'admin/preloader') show
@elseif(request()->path() == 'admin/basicinfo') show
@elseif(request()->path() == 'admin/support') show
@elseif(request()->path() == 'admin/social') show
@elseif(request()->is('admin/social/*')) show
@elseif(request()->path() == 'admin/heading') show
@elseif(request()->path() == 'admin/script') show
@elseif(request()->path() == 'admin/seo') show
@elseif(request()->path() == 'admin/maintainance') show
@elseif(request()->path() == 'admin/cookie-alert') show
@elseif(request()->path() == 'admin/mail-from-admin') show
@elseif(request()->path() == 'admin/mail-to-admin') show
@elseif(request()->routeIs('admin.featuresettings')) show
@elseif(request()->path() == 'admin/email-templates') show
@elseif(request()->routeIs('admin.email.editTemplate')) show
@elseif(request()->path() == 'admin/languages') show
@elseif(request()->is('admin/language/*/edit')) show
@elseif(request()->is('admin/language/*/edit/keyword')) show
@elseif(request()->path() == 'admin/gateways') show
@elseif(request()->path() == 'admin/offline/gateways') show
@elseif(request()->path() == 'admin/backup') show
@elseif(request()->path() == 'admin/sitemap') show @endif"
                            id="basic">
                            <ul class="nav nav-collapse">
                                <li class="@if (request()->path() == 'admin/basicinfo') active @endif">
                                    <a href="{{ route('admin.basicinfo') }}">
                                        <span class="sub-item">General Settings</span>
                                    </a>
                                </li>
                                <li class="submenu">
                                    <a data-toggle="collapse" href="#emailset"
                                        aria-expanded="{{ request()->path() == 'admin/mail-from-admin' || request()->path() == 'admin/mail-to-admin' || request()->path() == 'admin/email-templates' || request()->routeIs('admin.email.editTemplate') ? 'true' : 'false' }}">
                                        <span class="sub-item">Email Settings</span>
                                        <span class="caret"></span>
                                    </a>
                                    <div class="collapse {{ request()->path() == 'admin/mail-from-admin' || request()->path() == 'admin/mail-to-admin' || request()->path() == 'admin/email-templates' || request()->routeIs('admin.email.editTemplate') ? 'show' : '' }}"
                                        id="emailset" style="">
                                        <ul class="nav nav-collapse subnav">
                                            <li class="@if (request()->path() == 'admin/mail-from-admin') active @endif">
                                                <a href="{{ route('admin.mailFromAdmin') }}">
                                                    <span class="sub-item">Mail from Admin</span>
                                                </a>
                                            </li>
                                            <li class="@if (request()->path() == 'admin/mail-to-admin') active @endif">
                                                <a href="{{ route('admin.mailToAdmin') }}">
                                                    <span class="sub-item">Mail to Admin</span>
                                                </a>
                                            </li>
                                            <li
                                                class="@if (request()->path() == 'admin/email-templates') active
                    @elseif(request()->routeIs('admin.email.editTemplate')) active @endif">
                                                <a href="{{ route('admin.email.templates') }}">
                                                    <span class="sub-item">Email Templates</span>
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </li>
                                <li class="@if (request()->path() == 'admin/file-manager') active @endif">
                                    <a href="{{ route('admin.file-manager') }}">
                                        <span class="sub-item">File Manager</span>
                                    </a>
                                </li>
                                <li class="@if (request()->path() == 'admin/logo') active @endif">
                                    <a href="{{ route('admin.logo') }}">
                                        <span class="sub-item">Logo & Images</span>
                                    </a>
                                </li>
                                <li class="@if (request()->path() == 'admin/preloader') active @endif">
                                    <a href="{{ route('admin.preloader') }}">
                                        <span class="sub-item">Preloader</span>
                                    </a>
                                </li>
                                <li class="@if (request()->routeIs('admin.featuresettings')) active @endif">
                                    <a href="{{ route('admin.featuresettings') . '?language=' . $default->code }}">
                                        <span class="sub-item">Preferences</span>
                                    </a>
                                </li>
                                <li class="@if (request()->path() == 'admin/support') active @endif">
                                    <a href="{{ route('admin.support') . '?language=' . $default->code }}">
                                        <span class="sub-item">Support Informations</span>
                                    </a>
                                </li>
                                <li
                                    class="@if (request()->path() == 'admin/social') active
        @elseif(request()->is('admin/social/*')) active @endif">
                                    <a href="{{ route('admin.social.index') }}">
                                        <span class="sub-item">Social Links</span>
                                    </a>
                                </li>
                                <li class="@if (request()->path() == 'admin/heading') active @endif">
                                    <a href="{{ route('admin.heading') . '?language=' . $default->code }}">
                                        <span class="sub-item">Page Headings</span>
                                    </a>
                                </li>
                                <li
                                    class="
    @if (request()->path() == 'admin/gateways') selected
    @elseif(request()->path() == 'admin/offline/gateways') selected @endif">
                                    <a data-toggle="collapse" href="#gateways">
                                        <span class="sub-item">Payment Gateways</span>
                                        <span class="caret"></span>
                                    </a>
                                    <div class="collapse
    @if (request()->path() == 'admin/gateways') show
    @elseif(request()->path() == 'admin/offline/gateways') show @endif"
                                        id="gateways">
                                        <ul class="nav nav-collapse subnav">
                                            <li class="@if (request()->path() == 'admin/gateways') active @endif">
                                                <a href="{{ route('admin.gateway.index') }}">
                                                    <span class="sub-item">Online Gateways</span>
                                                </a>
                                            </li>
                                            <li class="@if (request()->path() == 'admin/offline/gateways') active @endif">
                                                <a
                                                    href="{{ route('admin.gateway.offline') . '?language=' . $default->code }}">
                                                    <span class="sub-item">Offline Gateways</span>
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </li>
                                <li
                                    class="
@if (request()->path() == 'admin/languages') active
@elseif(request()->is('admin/language/*/edit')) active
@elseif(request()->is('admin/language/*/edit/keyword')) active @endif">
                                    <a href="{{ route('admin.language.index') }}">
                                        <span class="sub-item">Language</span>
                                    </a>
                                </li>
                                <li class="@if (request()->path() == 'admin/script') active @endif">
                                    <a href="{{ route('admin.script') }}">
                                        <span class="sub-item">Plugins</span>
                                    </a>
                                </li>
                                <li class="@if (request()->path() == 'admin/seo') active @endif">
                                    <a href="{{ route('admin.seo') . '?language=' . $default->code }}">
                                        <span class="sub-item">SEO Information</span>
                                    </a>
                                </li>
                                <li class="@if (request()->path() == 'admin/maintainance') active @endif">
                                    <a href="{{ route('admin.maintainance') }}">
                                        <span class="sub-item">Maintenance Mode</span>
                                    </a>
                                </li>

                                <li class="@if (request()->path() == 'admin/cookie-alert') active @endif">
                                    <a href="{{ route('admin.cookie.alert') . '?language=' . $default->code }}">
                                        <span class="sub-item">Cookie Alert</span>
                                    </a>
                                </li>

                                <li
                                    class="
@if (request()->path() == 'admin/backup') selected
@elseif(request()->path() == 'admin/sitemap') selected @endif">
                                    <a data-toggle="collapse" href="#misc">
                                        <span class="sub-item">MISC</span>
                                        <span class="caret"></span>
                                    </a>
                                    <div class="collapse
@if (request()->path() == 'admin/backup') show
@elseif(request()->path() == 'admin/sitemap') show @endif"
                                        id="misc">
                                        <ul class="nav nav-collapse subnav">
                                            <li class="
    @if (request()->path() == 'admin/sitemap') selected @endif">
                                                <a
                                                    href="{{ route('admin.sitemap.index') . '?language=' . $default->code }}">
                                                    <span class="sub-item">Sitemap</span>
                                                </a>
                                            </li>
                                            <li class="
@if (request()->path() == 'admin/backup') selected @endif">
                                                <a href="{{ route('admin.backup.index') }}">
                                                    <span class="sub-item">Database Backup</span>
                                                </a>
                                            </li>
                                            <li>
                                                <a href="{{ route('admin.cache.clear') }}">
                                                    <span class="sub-item">Clear Cache</span>
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </li>
                @endif


                @if (empty($admin->role) || (!empty($permissions) && in_array('Admins Management', $permissions)))
                    {{-- Admins Management --}}
                    <li
                        class="nav-item
@if (request()->path() == 'admin/roles') active
@elseif(request()->is('admin/role/*/permissions/manage')) active
@elseif(request()->path() == 'admin/users') active
@elseif(request()->is('admin/user/*/edit')) active @endif">
                        <a data-toggle="collapse" href="#adminsManagement">
                            <i class="fas fa-users-cog"></i>
                            <p>Admins Management</p>
                            <span class="caret"></span>
                        </a>
                        <div class="collapse
@if (request()->path() == 'admin/roles') show
@elseif(request()->is('admin/role/*/permissions/manage')) show
@elseif(request()->path() == 'admin/users') show
@elseif(request()->is('admin/user/*/edit')) show @endif"
                            id="adminsManagement">
                            <ul class="nav nav-collapse">
                                <li
                                    class="
    @if (request()->path() == 'admin/roles') active
    @elseif(request()->is('admin/role/*/permissions/manage')) active @endif">
                                    <a href="{{ route('admin.role.index') }}">
                                        <span class="sub-item">Role Management</span>
                                    </a>
                                </li>
                                <li
                                    class="
@if (request()->path() == 'admin/users') active
@elseif(request()->is('admin/user/*/edit')) active @endif">
                                    <a href="{{ route('admin.user.index') }}">
                                        <span class="sub-item">Admins</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                @endif



                @if (empty($admin->role) || (!empty($permissions) && in_array('Client Feedbacks', $permissions)))
                    {{-- Client Feedbacks --}}
                    <li class="nav-item @if (request()->path() == 'admin/feedbacks') active @endif">
                        <a href="{{ route('admin.client_feedbacks') }}">
                            <i class="fas fa-pen-fancy"></i>
                            <p>Client Feedbacks</p>
                        </a>
                    </li>
                @endif
            </ul>
        </div>
    </div>
</div>
