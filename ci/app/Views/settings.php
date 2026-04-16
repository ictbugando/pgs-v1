<?php
    $disGroup   = "disabled";
    $disEmail   = "disabled";
    $disPhone   = "disabled";
    $disUsname  = "disabled";

    if( $permObj->groupChange ){
        $disGroup   = "";
    }

    if( $permObj->emailChange ){
        $disEmail   = "";
        $disPhone   = "";
        $disUsname  = "";
    }
    
?>
<h3><?php echo $sesionLanguage[0]->settingsHeaderLabel; ?></h3>
<hr />
<div class="container-fluid">
    <?php if( !$user->id ): ?>
        <div class="row">
            <div class="col-md">
                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    <strong>No record found!</strong><br /> The user does not exist or has been deleted.
                </div>           
            </div>
        </div>
    <?php else: ?>
        <div class="row">
            <div class="col-md-6">
                <table class="table table-striped table-bordered">
                    <tr>
                        <td><b>Registered: </b> <?php echo date_convert($userData->registerDate); ?></td>
                        <td><b>Last Login: </b> <?php echo date_convert($userData->lastvisitDate); ?></td>
                    </tr>
                </table>
            </div>
        </div>
         <div class="row">
            <?php if( $permObj->profChange ): ?>
            <div class="col-md">
                <h5 class=""><?php echo $sesionLanguage[0]->settingsProfileHeaderLabel; ?></h5>
                <form method="post" action="<?php echo base_url('dashboard/updateuserprofile'); ?>" autocomplete="off">
                <table class="table table-striped table-bordered">
                    <tbody>
                        <tr>
                            <th><?php echo $sesionLanguage[0]->settingsUpdateNameLabel; ?></th>
                            <td>
                                <div class="input-group">
                                    <input type="text" value="<?php echo $user->name ?>" name="name" class="form-control" placeholder="Full Names">
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <th><?php echo $sesionLanguage[0]->settingsUpdateUsNameLabel; ?></th>
                            <td>
                                <div class="input-group"> 
                                    <input type="text" <?php echo $disUsname; ?> value="<?php echo $user->username ?>"name="username" class="form-control" placeholder="Username">
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <th><?php echo $sesionLanguage[0]->settingsUpdateEmailLabel; ?></th>
                            <td>
                                <div class="input-group"> 
                                    <input type="text" <?php echo $disEmail; ?> name="email" value="<?php echo $user->email ?>" class="form-control" placeholder="Email">
                                </div>			
                            </td>
                        </tr>
                        <tr>
                            <th><?php echo $sesionLanguage[0]->settingsUpdateGroupLabel; ?></th>
                            <td>
                                <div class="input-group"> 
                                    <select class="form-select " name="usergroup" id="usergroup" <?php echo $disGroup; ?> > 
                                        <option value="99">Not Set</option>
                                        <?php foreach($userTypes AS $userType): ?>
                                            <?php if($userType->id == $userData->group_id): ?>
                                                <option selected value="<?php echo $userType->id; ?>"><?php echo $userType->title; ?></option>
                                            <?php else: ?>
                                                <option value="<?php echo $userType->id; ?>"><?php echo $userType->title; ?></option>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <th><?php echo $sesionLanguage[0]->settingsUpdateLanguageLabel; ?></th>
                            <td>
                                <div class="input-group"> 
                                    <select class="form-select " name="userlang" id="userlang"> 
                                        <option value="99">Not Set</option>
                                        <?php foreach($languages AS $lang): ?>
                                            <?php if($lang->id == $userData->lang_id): ?>
                                                <option selected value="<?php echo $lang->id; ?>"><?php echo $lang->lang_title; ?></option>
                                            <?php else: ?>
                                                <option value="<?php echo $lang->id; ?>"><?php echo $lang->lang_title; ?></option>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </select>
                                </div> 		
                            </td>
                        </tr>
                        <tr>
                            <th><?php echo $sesionLanguage[0]->settingsUpdatePhoneLabel; ?></th>
                            <td>
                                <div class="input-group"> 
                                    <input type="text" <?php echo $disPhone; ?> name="phone" value="<?php echo $userData->phone ?>" class="form-control" placeholder="Phone Number">
                                </div>			
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2">
                                <div id="edtProfErr" class="alert alert-warning alert-dismissible fade show formPostErr hideMe" role="alert">
                                    <strong>Message!</strong><br />
                                    <span id="1alertMsg">&nbsp;</span>
                                </div>
                                <div class="d-grid gap-2 col-6 mx-auto">
                                    <button type="button" id="1" class="btn btn-primary updateuser-btn"><b><?php echo $sesionLanguage[0]->settingsUpdateProfButtonLabel; ?></b></button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <input type="hidden" name="userid" class="form-control" value="<?php echo $userData->id ?>" />
                </form>
            </div>
            <?php endif; ?>

            <div class="col-md">
                <h5 class="col-6 max-auto"><?php echo $sesionLanguage[0]->settingsUpdatePassHeaderLabel; ?></h5>
                <?php if( $permObj->passChange ): ?>
                    <form method="post" action="<?php echo base_url('dashboard/updateuserpass'); ?>" autocomplete="off">
                    <table class="table table-striped table-bordered">
                        <tbody>
                            <tr>
                                <th><?php echo $sesionLanguage[0]->settingsUpdateOldPassLabel; ?></th>
                                <td>
                                    <div class="input-group"> 
                                        <input type="password" name="passold" class="form-control" placeholder="Old Password" autocomplete="off">
                                    </div>			
                                </td>
                            </tr>                    
                            <tr>
                                <th><?php echo $sesionLanguage[0]->settingsUpdateNewPassLabel; ?></th>
                                <td>
                                    <div class="input-group">
                                        <input type="password" name="pass1" class="form-control" placeholder="New Password" autocomplete="off">
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <th><?php echo $sesionLanguage[0]->settingsUpdateRepeatPassLabel; ?></th>
                                <td>
                                    <div class="input-group"> 
                                        <input type="password" name="pass2" class="form-control" placeholder="Repeat Password" autocomplete="off">
                                    </div>			
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2">
                                    <div  class="alert alert-warning alert-dismissible fade show hideMe" id="edtPassErr" role="alert">
                                        <strong>Message!</strong><br />
                                        <span id="2alertMsg">&nbsp;</span>
                                    </div>
                                    <div class="d-grid gap-2 col-6 mx-auto">
                                        <button type="button" id="2" class="btn btn-primary updateuser-btn"><b><?php echo $sesionLanguage[0]->settingsUpdatePassButtonLabel; ?></b></button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <input type="hidden" name="userid" class="form-control" value="<?php echo $userData->id ?>" />
                    </form>
                <?php elseif( $permObj->passSendReset ): ?>
                    <div class="alert alert-dark" role="alert">
                    An password reset email will be sent to the user when you click send rest code below.
                    </div>                   
                    <div class="d-grid gap-2 col-6 mx-auto">
                        <button type="button" class="btn btn-primary"><b>Send Reset Code Email.</b></button>
                    </div>
                    <hr />
                    <div class="d-grid gap-2 col-6 mx-auto">
                        <a href="<?= base_url("dashboard/archiveuser/".$user->id); ?>" type="button" class="btn btn-danger"><b>Delete/Archive Account.</b></a>
                    </div>
                <?php endif; ?>
            </div>
            
        </div>
        <div class="row">
            <div class="col-md">
                <h4>Activities History</h4>
                <hr />
                <?php if( $permObj->logsView ): ?>
                    <p>Permited to view this user's logs</p>
                <?php else: ?>
                    <div class="alert alert-warning alert-dismissible fade show" role="alert">
                        <strong>Permission Denied!</strong><br /> You are not allowed to view this user's logs.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>