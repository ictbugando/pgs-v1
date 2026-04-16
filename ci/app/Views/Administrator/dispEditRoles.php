<form method="post" action="<?php echo base_url('dashboard/updategrouproles'); ?>">
    <h3>ASSIGN ROLES TO GROUP</h3>
    
    <?php if( isset($groupRolesArr["group_id"]) ): ?>
        <?php
            $currRoles  = $groupRolesArr["roleArr"];
        ?>
        <h5>GROUP :: <?php echo strtoupper($groupRolesArr["title"]); ?></h5>
        <table class="table table-bordered">
            <tr>
                <td>
                    <h6 class="p-3 mb-2 bg-dark text-white">GLOBAL ROLES</h6>
                    <ul>
                        <?php foreach($systemRoles[1] AS $rolesArr): ?>
                            <?php
                                $isChecked  = "";
                                if (in_array( $rolesArr->role_id, $currRoles)) {
                                    $isChecked  = "checked";
                                }
                            ?>
                            <li>
                                <div class="form-check">
                                    <input class="form-check-input" <?php echo $isChecked; ?> type="checkbox" name="roles[]" value="<?php echo $rolesArr->role_id; ?>" >
                                    <label class="form-check-label"><?php echo $rolesArr->role_name; ?></label>
                                </div>                       
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    
                    <h6 class="p-3 mb-2 bg-dark text-white">TRANSACTIONS ROLES</h6>
                    <ul>
                        <?php foreach($systemRoles[2] AS $rolesArr): ?>
                            <?php
                                $isChecked  = "";
                                if (in_array( $rolesArr->role_id, $currRoles)) {
                                    $isChecked  = "checked";
                                }
                            ?>
                            <li>
                                <div class="form-check">
                                    <input class="form-check-input" <?php echo $isChecked; ?> type="checkbox" name="roles[]" value="<?php echo $rolesArr->role_id; ?>" >
                                    <label class="form-check-label"><?php echo $rolesArr->role_name; ?></label>
                                </div>                       
                            </li>
                        <?php endforeach; ?>
                    </ul>

                    <h6 class="p-3 mb-2 bg-dark text-white">REFUND ROLES</h6>
                    <ul>
                        <?php foreach($systemRoles[4] AS $rolesArr): ?>
                            <?php
                                $isChecked  = "";
                                if (in_array( $rolesArr->role_id, $currRoles)) {
                                    $isChecked  = "checked";
                                }
                            ?>
                            <li>
                                <div class="form-check">
                                    <input class="form-check-input" <?php echo $isChecked; ?> type="checkbox" name="roles[]" value="<?php echo $rolesArr->role_id; ?>" >
                                    <label class="form-check-label"><?php echo $rolesArr->role_name; ?></label>
                                </div>                       
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </td>
                <td>
                    <h6 class="p-3 mb-2 bg-dark text-white">BILLING ROLES</h6>
                    <ul>
                        <?php foreach($systemRoles[5] AS $rolesArr): ?>
                            <?php
                                $isChecked  = "";
                                if (in_array( $rolesArr->role_id, $currRoles)) {
                                    $isChecked  = "checked";
                                }
                            ?>
                            <li>
                                <div class="form-check">
                                    <input class="form-check-input" <?php echo $isChecked; ?> type="checkbox" name="roles[]" value="<?php echo $rolesArr->role_id; ?>" >
                                    <label class="form-check-label"><?php echo $rolesArr->role_name; ?></label>
                                </div>                       
                            </li>
                        <?php endforeach; ?>
                    </ul>

                    <h6 class="p-3 mb-2 bg-dark text-white">REPORTS ROLES</h6>
                    <ul>
                        <?php foreach($systemRoles[3] AS $rolesArr): ?>
                            <?php
                                $isChecked  = "";
                                if (in_array( $rolesArr->role_id, $currRoles)) {
                                    $isChecked  = "checked";
                                }
                            ?>
                            <li>
                                <div class="form-check">
                                    <input class="form-check-input" <?php echo $isChecked; ?> type="checkbox" name="roles[]" value="<?php echo $rolesArr->role_id; ?>" >
                                    <label class="form-check-label"><?php echo $rolesArr->role_name; ?></label>
                                </div>                       
                            </li>
                        <?php endforeach; ?>
                    </ul>

                </td>
            </tr>
        </table>
        <input type="hidden" name="currRoleGroup" value="<?php echo $groupRolesArr["group_id"]; ?>"/>
        <div class="d-grid gap-2 col-6 mx-auto">
            <button type="submit" class="btn btn-primary">UPDATE ROLES</button>
        </div>
    <?php endif; ?>
</form>