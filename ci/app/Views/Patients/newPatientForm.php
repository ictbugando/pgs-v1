<?php 
    $sesionLanguage = get_instance()->sesionLanguage;
?>
<script>pickBMCDate();</script>
<h4>Add New Patient Record</h4>
<hr>
<form method="post" action="<?php echo base_url('patients/processnewpat'); ?>">
    <table class="table table-striped table-bordered">
        <tbody>
            <tr><th colspan="3"><?php echo $sesionLanguage[0]->newPatNameHeader; ?></th></tr>
            <tr>
                <td colspan="3">
                    <div class="row align-items-start">
                        <div class="col form-group">
                            <label><?php echo $sesionLanguage[0]->newPatRecordSName; ?></label>
                            <input type="text"  name="sname" class="form-control" placeholder = "<?php echo $sesionLanguage[0]->newPatRecordSName ; ?>" />
                        </div>

                        <div class="col form-group">
                            <label><?php echo $sesionLanguage[0]->newPatRecordFName; ?></label>
                            <input type="text"  name="names" class="form-control" placeholder = "<?php echo $sesionLanguage[1]->newPatRecordFName ; ?>" />
                        </div>

                        <div class="col form-group">
                            <label><?php echo $sesionLanguage[0]->newPatFileNum; ?></label>
                            <input type="text"  name="patNum" class="form-control" placeholder = "<?php echo $sesionLanguage[1]->newPatFileNum; ?>" />
                        </div>                                    
                    </div>
                </td>
            </tr>
            <tr>
                <td colspan="3">
                    <div class="row align-items-start">
                        <div class="col form-group">
                            <label><?php echo $sesionLanguage[0]->newPatPhone; ?></label>
                            <input type="text"  name="phone" class="form-control" placeholder = "<?php echo $sesionLanguage[1]->newPatPhone; ?>" />
                        </div>
                        <!--
                        <div class="col form-group">
                            <label><?php echo $sesionLanguage[0]->newPatDOB; ?></label>
                            <input type="text"  name="patDob" id="datepicker2" class="datepicker_input form-control" placeholder="DD/MM/YYYY" />
                        </div>
                        -->
                    </div>
                </td>
            </tr>
            <!--
            <tr><th colspan="3"><?php echo $sesionLanguage[0]->newPatKinHeader; ?></th></tr>
            <tr>
                <td colspan="3">
                    <div class="row align-items-start">
                        <div class="col form-group">
                            <label><?php echo $sesionLanguage[0]->newPatKin; ?></label>
                            <input type="text"  name="kinName" class="form-control" placeholder = "<?php echo $sesionLanguage[1]->newPatKin; ?>" />
                        </div>

                        <div class="col form-group">
                            <label><?php echo $sesionLanguage[0]->newPatKinPhone; ?></label>
                            <input type="text"  name="kinPhone" class="form-control" placeholder = "<?php echo $sesionLanguage[1]->newPatKinPhone; ?>" />
                        </div>                                    
                    </div>
                </td>
            </tr>
            -->
            <!--
            <tr><th colspan="3"><?php echo $sesionLanguage[0]->newPatIdDocsHeader; ?></th></tr>
            <tr>
                <td colspan="3">
                    <div class="row align-items-start">
                        <div class="col form-group">
                            <label><?php echo $sesionLanguage[0]->newPatIdType; ?></label>
                            <select class="form-select " name="idType" id="idType" > 
                                <option value="0">Not Set</option>
                                <?php foreach($idTypeArr AS $idArr): ?>
                                    <option value="<?php echo $idArr->type_id; ?>"><?php echo $idArr->id_name; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col form-group">
                            <label><?php echo $sesionLanguage[0]->newPatIdNum; ?></label>
                            <div class="input-group">
                                <input type="text"  name="idNum" class="form-control" placeholder = "<?php echo $sesionLanguage[1]->newPatIdNum; ?>" />
                            </div>
                        </div>                                    
                    </div>
                </td>
            </tr>
            <tr><th colspan="3">Patient Address<?php //echo $sesionLanguage->newPatIdNum; ?></th></tr>
            <tr>
                <td colspan="3">
                    <div class="row align-items-start">
                        <div class="col form-group">
                            <label><?php echo $sesionLanguage[0]->newPatLocMkoa; ?></label>
                            <select class="form-select " name="mkoaselect" id="mkoaselect" > 
                                <option value="99">Not Set</option>
                                <?php foreach($mkoaArray AS $mkoaAr): ?>
                                    <option value="<?php echo $mkoaAr->mkoa_id; ?>"><?php echo $mkoaAr->mkoa_name; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col form-group">
                            <label><?php echo $sesionLanguage[0]->newPatLocWilaya; ?></label>
                            <select class="form-select " name="wilayaselect" id="wilayaselect" > 
                                <option value="0">Not Set</option>
                            </select>
                        </div>

                        <div class="col form-group">
                            <label><?php echo $sesionLanguage[0]->newPatLocKata; ?></label>
                            <select class="form-select " name="kataselect" id="kataselect" > 
                                <option value="0">Not Set</option>
                            </select>
                        </div>

                    </div>
                </td>
            </tr>
            -->
        </tbody>
    </table>

    <div id="ajaxAlertMsg">&nbsp;</div>

    <div class="d-grid gap-2 col-6 mx-auto">
        <button type="submit" class="btn btn-primary newpatsubmit"><?php echo $sesionLanguage[0]->newPatRecordLabelSubmit; ?></button>
    </div>
</form>

<input type="hidden" name="fetchLocsUrl" id="fetchLocsUrl" value="<?php echo base_url('patients/fetchLocations'); ?>" />
<input type="hidden" name="postnewpat" id="postnewpat" value="<?php echo base_url('patients/processnewpat'); ?>" />