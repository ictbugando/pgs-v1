<?php
//var_dump($userData);
//die();
?>
<form method="post" action="<?php echo base_url('dashboard/postnewuser'); ?>">
    <h4><?php echo $sesionLanguage[0]->newUserRecordLabelHeader; ?></h4>
    <table class="table table-striped table-bordered">
        <tbody>
            <tr>
                <th><?php echo $sesionLanguage[0]->settingsUpdateNameLabel; ?></th>
                <td>
                    <div class="input-group">
                        <input type="text"  name="name" class="form-control" placeholder = "Full Names" />
                    </div>
                </td>
            </tr>	
            <tr>
                <th><?php echo $sesionLanguage[0]->settingsUpdateUsNameLabel; ?></th>
                <td>
                    <div class="input-group"> 
                        <input type="text"  name="username" class="form-control" placeholder = "Username" />
                    </div>
                </td>
            </tr>
            <tr>
                <th><?php echo $sesionLanguage[0]->settingsUpdateEmailLabel; ?></th>
                <td>
                    <div class="input-group"> 
                        <input type="text"  name="email" class="form-control" placeholder = "Email" />
                    </div>			
                </td>
            </tr>
            <tr>
                <th><?php echo $sesionLanguage[0]->settingsUpdateGroupLabel; ?></th>
                <td>
                    <div class="input-group"> 
                        <select class="form-select " name="usergroup" id="usergroup" > 
                            <option value="99">Not Set</option>
                            <?php foreach($userTypes AS $userType): ?>
                                <option value="<?php echo $userType->id; ?>"><?php echo $userType->title; ?></option>
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
                                <option value="<?php echo $lang->id; ?>"><?php echo $lang->lang_title; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div> 		
                </td>
            </tr>            
            <tr>
                <th>Phone</th>
                <td>
                    <div class="input-group"> 
                        <input type="text"  name="phone" class="form-control" placeholder = "Phone Number" />
                    </div>			
                </td>
            </tr>
            <tr>
                <th>Password</th>
                <td>
                    <div class="input-group"> 
                        <input type="password"  name="pass1" class="form-control" placeholder = "Password" />
                    </div>			
                </td>
            </tr>
            <tr>
                <th>Repeat Password</th>
                <td>
                    <div class="input-group"> 
                        <input type="password"  name="pass2" class="form-control" placeholder = "Repeat Password" />
                    </div>			
                </td>
            </tr>                        
        </tbody>
    </table>

    <div id="formPostErr">
        <div  class="alert alert-warning alert-dismissible fade show formPostErr" role="alert">
        <strong>Message!</strong><br />
        <span id="alertMsg">&nbsp;</span>
        </div>
    </div>
    <div class="mySpinnerForm">
        <div class="spinner-grow text-primary" role="status"></div>
        <div class="spinner-grow text-primary" role="status"></div>
        <div class="spinner-grow text-primary" role="status"></div>
    </div>   
    
    <div class="d-grid gap-2 col-6 mx-auto">
        <button type="submit" class="btn btn-primary newuser-btn"><?php echo $sesionLanguage[0]->newUserRecordLabelUpdate; ?></button>
    </div>   

</form>