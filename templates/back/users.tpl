{include file='_header.tpl'}

<div class='main-content'>
    <div class='container-fluid'>
        <div class='row justify-content-center'>
            <div class='col-12 col-lg-10 col-xl-8'>


                <div class='header mt-md-5'>
                  <div class='header-body'>
                    <div class='row align-items-center'>
                      <div class='col'><h1 class='header-title'>Пользователи</h1></div>
                      <div class='col text-right'><a href='#' class='btn btn-sm btn-rounded-circle btn-primary tip' data-toggle='modal' data-target='#modalAddUser' title='Добавить пользователя'>+</a></div>
                    </div>
                  </div>
                </div>


                <div class='card'>
                  <table class='table card-table table-nowrap'>
                    <thead class='thead-light'>
                      <tr>
                        <th scope='col'>Фамилия Имя</th>
                        <th scope='col'>Логин</th>
                        <th scope='col'>Группа</th>
                        <th scope='col'></th>
                      </tr>
                    </thead>
                    <tbody>
                        {foreach name=p source=#users}
                            {if #p.group_name == 'Unregistered'}{code continue}{/if}
                            <tr>
                              <td class='align-middle' scope='row'>
                                  {#p.lastname} {#p.firstname} {#p.middlename}
                                  {if #user.id == #p.id}<span class='ml-1 badge badge-dark'>Это вы</span>{/if}
                              </td>
                              <td class='align-middle'>{#p.login}</td>
                              <td class='align-middle'>{#p.group_name}</td>
                              <td class='text-right align-middle'>
                                 {if 0}<a href='?edit={#p.id}' class='btn btn-primary btn-sm'><span class='fe fe-edit-2'></span></a>{/if}
                                 {if #user.id != #p.id}<a href='?delete={#p.id}' onclick="return confirm('Подтверждение');" class='btn btn-danger btn-sm ml-2'><span class='fe fe-trash'></span></a>{/if}
                              </td>
                            </tr>
                        {/foreach}
                    </tbody>
                  </table>
                </div>

                <div class='modal fade' id='modalAddUser' tabindex='-1' role='dialog' aria-hidden='true'>
                    <div class='modal-dialog modal-dialog-centered' role='document'>
                        <div class='modal-content mb-2'>
                            <div class='modal-card card'>
                                <div class='card-header'>
                                    <div class='row align-items-center'>
                                        <div class='col'><h4 class='card-header-title'>Добавить пользователя</h4></div>
                                        <div class='col-auto'><button type='button' class='close' data-dismiss='modal' aria-label='Закрыть'><span aria-hidden='true'>×</span></button></div>
                                    </div>
                                </div>
                                <form method='post'>
                                    <div class='card-body'>
                                        <div class='row form-group'>
                                            <div class='col'>
                                                <label>Имя</label>
                                                <input name='firstname' type='text' class='form-control' required/>
                                            </div>
                                            <div class='col'>
                                                <label>Фамилия</label>
                                                <input name='lastname' type='text' class='form-control' required/>
                                            </div>
                                        </div>
                                        <div class='row form-group'>
                                            <div class='col'>
                                                <label>Логин</label>
                                                <input name='login' type='text' class='form-control' required/>
                                            </div>
                                            <div class='col'>
                                                <label>Почта</label>
                                                <input name='email' type='email' class='form-control' required/>
                                            </div>
                                        </div>
                                        <div class='form-group'>
                                            <label>Группа</label>
                                            <select name='group_id' class='form-control' data-toggle='select' data-minimum-results-for-search='Infinity' data-placeholder='' required>
                                                <option></option>
                                                {foreach name=s source=#groups}
                                                    {if #s.name == 'Unregistered'}{code continue}{/if}
                                                    <option value='{#s.id}'>{#s.name}</option>
                                                {/foreach}
                                            </select>
                                        </div>
                                        <div class='form-group'>
                                            <label>Пароль</label>
                                            <input name='password' type='password' class='form-control'/>
                                        </div>
                                    </div>
                                    <div class='card-footer'>
                                        <input type='submit' class='btn btn-block btn-primary' value='Сохранить'/>
                                        <input type='hidden' name='action' value='addUser'/>
                                        <input type='hidden' name='back' value='/back/users'/>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

            </div>


        </div>
    </div>
</div>

{include file='_footer.tpl'}
