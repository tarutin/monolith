{code #hide_nav = true}
{code #body_class = 'd-flex align-items-center bg-auth border-top border-top-2 border-primary'}
{include file='_header.tpl'}

    <div class='container'>
        <div class='row justify-content-center'>
            <div class='col-12 col-md-5 col-xl-4 my-5'>
                <h1 class='display-4 text-center mb-3'>{#settings.site_name}</h1>
                <p class='text-muted text-center mb-5'>Если у вас есть доступ — авторизуйтесь.</p>
                <form method='post'>
                    <div class='my-5'><input type='text' name='login' class='form-control' placeholder='Логин' required autocomplete='new-password' autocomplete='off'/></div>
                    <div class='my-5'><input type='password' name='password' class='form-control' placeholder='Пароль' required autocomplete='new-password' autocomplete='off'/></div>
                    <div class='my-5'><button class='btn btn-lg btn-block btn-primary'>Войти</button></div>
    				<input type='hidden' name='action' value='doAuth'/>
    				<input type='hidden' name='go' value='{#url}'/>
                </form>
            </div>
        </div>
    </div>

{include file='_footer.tpl'}
