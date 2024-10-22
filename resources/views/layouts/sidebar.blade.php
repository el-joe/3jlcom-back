    <div id="sidebar" class="active">
        <div class="sidebar-wrapper active">
            <div class="sidebar-header position-relative">
                <div class="d-flex">
                    <div class="logo">
                        <a href="{{ url('home') }}">
                            <img src="{{ url('assets/images/logo/logo.png') }}" alt="Logo" srcset="">
                        </a>
                    </div>
                </div>
            </div>
            <div class="sidebar-menu">
                <ul class="menu">
                    @if (has_permissions('read', 'dashboard'))
                        <li class="sidebar-item">
                            <a href="{{ url('home') }}" class='sidebar-link'>
                                <i class="fas fa-tachometer-alt"></i>
                                <span class="menu-item">{{ __('Dashboard') }}</span>
                            </a>
                        </li>
                    @endif

                    @if (has_permissions('read', 'property'))
                        <li class="sidebar-item">
                            <a href="{{ url('property') }}" class='sidebar-link'>
                                <i class="fas fa-car"></i>
                                <span class="menu-item">{{ __('Property') }}</span>
                            </a>
                        </li>
                    @endif

                    @if (has_permissions('read', 'customer'))
                        <li class="sidebar-item">
                            <a href="{{ url('customer') }}" class='sidebar-link'>
                                <i class="fas fa-users"></i>
                                <span class="menu-item">{{ __('Customer') }}</span>
                            </a>
                        </li>
                    @endif

                    @if (has_permissions('read', 'customer'))
                        <li class="sidebar-item">
                            <a href="{{ url('property-inquiry') }}" class='sidebar-link'>
                                <i class="fas fa-comments-dollar"></i>
                                <span class="menu-item">{{ __('Property Enquiries') }}</span>
                            </a>
                        </li>
                    @endif

                    {{--@if (has_permissions('read', 'customer'))
                        <li class="sidebar-item">
                            <a href="{{ url('personalized') }}" class='sidebar-link'>
                                <i class="fas fa-search"></i>
                                <span class="menu-item">{{ __('Personalized') }}</span>
                            </a>
                        </li>
                    @endif--}}

                    @if (has_permissions('read', 'categories') || has_permissions('read', 'unit'))
                        <li class="sidebar-item has-sub">
                            <a href="#" class='sidebar-link'>
                                <i class="fas fa-th-large"></i>
                                <span class="menu-item">{{ __('Specifications')}}</span>
                            </a>
                            <ul class="submenu" style="padding-left: 0rem">

                                @if (has_permissions('read', 'categories'))
                                <li class="submenu-item">
                                    <a href="{{ url('categories') }}">
                                        <i class="fas fa-caret-right"> </i>
                                        <span class="menu-item">{{ __('Categories') }}</span>
                                    </a>
                                </li>
                                @endif

                                @if (has_permissions('read', 'unit'))
                                <li class="submenu-item">
                                    <a href="{{ url('parameters') }}">
                                        <i class="fas fa-caret-right"> </i>
                                        <span class="menu-item">{{ __('Specifications') }}</span>
                                    </a>
                                </li>
                                @endif

                                @if (has_permissions('read', 'categories'))
                                <li class="submenu-item">
                                    <a href="{{ url('manufacturers') }}">
                                        <i class="fas fa-caret-right"> </i>
                                        <span class="menu-item">{{ __('Manufacturers') }}</span>
                                    </a>
                                </li>
                                @endif

                                @if (has_permissions('read', 'categories'))
                                <li class="submenu-item">
                                    <a href="{{ url('models') }}">
                                        <i class="fas fa-caret-right"> </i>
                                        <span class="menu-item">{{ __('Models') }}</span>
                                    </a>
                                </li>
                                @endif
                            </ul>
                        </li>
                    @endif

                    @if (has_permissions('read', 'slider'))
                        <li class="sidebar-item">
                            <a href="{{ url('slider') }}" class='sidebar-link'>
                                <i class="fas fa-images"></i>
                                <span class="menu-item">{{ __('Slider') }}</span>
                            </a>
                        </li>
                    @endif

                    <li class="sidebar-item">
                        <a href="{{ url('article') }}" class='sidebar-link'>
                            <i class="fas fa-newspaper"></i>
                            <span class="menu-item">{{ __('Article') }}</span>
                        </a>
                    </li>

                    <li class="sidebar-item">
                        <a href="{{ url('advertisement') }}" class='sidebar-link'>
                            <i class="fas fa-audio-description"></i>
                            <span class="menu-item">{{ __('Advertisement') }}</span>
                        </a>
                    </li>

                    <li class="sidebar-item">
                        <a href="{{ url('package') }}" class='sidebar-link'>
                            <i class="fas fa-archive"></i>
                            <span class="menu-item">{{ __('Packages') }}</span>
                        </a>
                    </li>


                    <li class="sidebar-item">
                        <a href="{{ url('package-requests') }}" class='sidebar-link'>
                            <i class="fas fa-archive"></i>
                            <span class="menu-item">{{ __('Package Requests') }}</span>
                        </a>
                    </li>
                    <li class="sidebar-item">
                        <a href="{{ url('verification-requests') }}" class='sidebar-link'>
                            <i class="fas fa-archive"></i>
                            <span class="menu-item">{{ __('Verification Requests') }}</span>
                        </a>
                    </li>

                    <li class="sidebar-item">
                        <a href="{{ url('payment') }}" class='sidebar-link'>
                            <i class="fas fa-money-check-alt"></i>
                            <span class="menu-item">{{ __('Payment') }}</span>
                        </a>
                    </li>

                    @if (has_permissions('read', 'notification'))
                        <li class="sidebar-item">
                            <a href="{{ url('notification') }}" class='sidebar-link'>
                                <i class="fas fa-bell"></i>
                                <span class="menu-item">{{ __('Notification') }}</span>
                            </a>
                        </li>
                    @endif

                    @if (has_permissions('read', 'customer'))
                         <li class="sidebar-item has-sub">
                            <a href="#" class='sidebar-link'>
                                <i class="fas fa-exclamation-circle"></i>
                                <span class="menu-item">{{ __('Users Reports') }}</span>
                            </a>
                            <ul class="submenu" style="padding-left: 0rem">

                                @if (has_permissions('read', 'customer'))
                                <li class="submenu-item">
                                    <a href="{{ url('users_reports') }}">
                                        <i class="fas fa-caret-right"> </i>
                                        <span class="menu-item">{{ __('Users Reports') }}</span>
                                    </a>
                                </li>
                                @endif

                                @if (has_permissions('read', 'customer'))
                                <li class="submenu-item">
                                    <a href="{{ url('report-reasons') }}">
                                        <i class="fas fa-caret-right"> </i>
                                        <span class="menu-item">{{ __('Report Reasons') }}</span>
                                    </a>
                                </li>
                                @endif
                            </ul>
                        </li>
                    @endif

                    @if (has_permissions('read', 'users_accounts') ||
                            has_permissions('read', 'about_us') ||
                            has_permissions('read', 'privacy_policy') ||
                            has_permissions('read', 'terms_condition'))
                        <li class="sidebar-item has-sub">
                            <a href="#" class='sidebar-link'>
                                <i class="fas fa-cogs"></i>
                                <span class="menu-item">{{ __('Settings') }}</span>
                            </a>
                            <ul class="submenu" style="padding-left: 0rem">
                                @if (has_permissions('read', 'system_settings'))
                                    <li class="submenu-item">
                                        <a href="{{ url('system-settings') }}">
                                            <i class="fas fa-caret-right"> </i>
                                            <span class="menu-item">{{ __('System Settings') }}</span>
                                        </a>
                                    </li>
                                @endif
                                <li class="submenu-item">
                                    <a href="{{ url('firebase_settings') }}">
                                        <i class="fas fa-caret-right"> </i>
                                        <span class="menu-item">{{ __('Firebase Settings') }}</span>
                                    </a>
                                </li>

                                <li class="submenu-item">
                                    <a href="{{ url('language') }}">
                                        <i class="fas fa-caret-right"> </i>
                                        <span class="menu-item">{{ __('Languages') }}</span>
                                    </a>
                                </li>
                                @if (has_permissions('read', 'users_accounts'))
                                    <li class="submenu-item">
                                        <a href="{{ url('users') }}">
                                            <i class="fas fa-caret-right"> </i>
                                            <span class="menu-item">{{ __('Users Accounts') }}</span>
                                        </a>
                                    </li>
                                @endif
                                @if (has_permissions('read', 'about_us'))
                                    <li class="submenu-item">
                                        <a href="{{ url('about-us') }}">
                                            <i class="fas fa-caret-right"> </i>
                                            <span class="menu-item">{{ __('About Us') }}</span>
                                        </a>
                                    </li>
                                @endif
                                @if (has_permissions('read', 'privacy_policy'))
                                    <li class="submenu-item">
                                        <a href="{{ url('privacy-policy') }}">
                                            <i class="fas fa-caret-right"> </i>
                                            <span class="menu-item">{{ __('Privacy Policy') }}</span>
                                        </a>
                                    </li>
                                @endif
                                @if (has_permissions('read', 'terms_condition'))
                                    <li class="submenu-item">
                                        <a href="{{ url('terms-conditions') }}">
                                            <i class="fas fa-caret-right"> </i>
                                            <span class="menu-item">{{ __('Terms & Condition') }}</span>
                                        </a>
                                    </li>
                                @endif
                            </ul>
                        </li>
                    @endif
                </ul>
            </div>
        </div>
    </div>
