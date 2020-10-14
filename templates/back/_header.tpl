<!doctype html>
<html>
    <head>
        <title>{if #meta.title}{#meta.title} &bull; {/if}Бекоффис &bull; {#settings.site_name}</title>
        <meta charset='utf-8'/>
        <meta name='viewport' content='width=device-width, initial-scale=1, shrink-to-fit=no'/>
        {code css([
            'templates/back/assets/fonts/feather/feather.min.css',
            'templates/back/assets/libs/flatpickr/dist/flatpickr.min.css',
            'templates/back/assets/libs/quill/dist/quill.core.css',
            'templates/back/assets/libs/select2/dist/css/select2.min.css',
            'templates/back/assets/libs/highlight.js/styles/vs2015.css',
            'templates/back/assets/css/theme-dark.min.css',
            'templates/back/assets/css/all.css',
        ])}
    </head>
    <body class='{#body_class}'>

        {if !#hide_nav}
            <nav class='navbar navbar-vertical fixed-left navbar-expand-md navbar-light' id='sidebar'>
              <div class='container-fluid'>

                <button class='navbar-toggler' type='button' data-toggle='collapse' data-target='#sidebarCollapse' aria-controls='sidebarCollapse' aria-expanded='false' aria-label='Toggle navigation'>
                  <span class='navbar-toggler-icon'></span>
                </button>

                <a class='navbar-brand' href='/back'><img src='/static/i/logo-icon.svg' style='opacity:.7;' class='navbar-brand-img mx-auto'/></a>

                <div class='collapse navbar-collapse' id='sidebarCollapse'>
                  <ul class='navbar-nav'>
                    {if #user.group == 'Admin'}<li class='nav-item {if #getUrl1 == 'users'}active{/if}'><a class='nav-link' href='/back/users'><span class='fe fe-lock'></span> Пользователи</a></li>{/if}
                    <li class='nav-item {if #getUrl1 == 'cal'}active{/if}'><a class='nav-link' href='/back/cal'><span class='fe fe-calendar'></span> Календарь</a></li>
                    <li class='nav-item {if #getUrl1 == 'actors'}active{/if}'><a class='nav-link' href='/back/actors'><span class='fe fe-users'></span> Актеры</a></li>
                    <li class='nav-item'>
                      <span class='nav-link'><i class='fe fe-layers'></i> Спектакли</span>
                      <div class='collapse show' id='sidebarDashboards'>
                        <ul class='nav nav-sm flex-column'>
                          {foreach name=s source=#shows}
                              <li class='nav-item'>
                                  <a href='/back/show/{#s.id}' class='nav-link {if #getUrl1 == 'show' && #s.id == #getUrl2}active{/if}'>
                                      {#s.name}
                                      {if #s.status == 'draft'}<span class='badge badge-dark ml-auto px-2'>Черновик</span>{/if}
                                      {if #s.top}<span class='badge badge-dark ml-auto px-2 tip' title='На главной'>&uarr;</span>{/if}
                                  </a>
                              </li>
                          {/foreach}
                          <li class='nav-item'><a href='/back/show?add' class='nav-link {if #getUrl1 == 'show' && isset(#getUrladd) && !#getUrl2}active{/if}'><i class='fe fe-plus-square'></i> Добавить</a></li>
                        </ul>
                      </div>
                    </li>
                    <li class='nav-item {if #getUrl1 == 'contact'}active{/if}'><a class='nav-link' href='/back/contact'><span class='fe fe-at-sign'></span> Контакты</a></li>
                  </ul>

                  <div class='mt-auto'></div>
                  <hr class="navbar-divider my-3">

                  <ul class='navbar-nav'>
                      <li class='nav-item'><a class='nav-link' href='/logout?return=/back'><span class='fe fe-log-out'></span> Выход</a></li>
                  </ul>

                </div>

              </div>
            </nav>
        {/if}
