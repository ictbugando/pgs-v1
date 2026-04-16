<nav>
    <div class="nav nav-tabs" id="nav-tab" role="tablist">
        <div style="margin-top:10px;"><h4><b>SYSTEM SETTINGS | <span>&nbsp;</span></b></h4></div>
        <button class="nav-link active" id="nav-txns-tab" data-bs-toggle="tab" data-bs-target="#nav-txns" type="button" role="tab" aria-controls="nav-txns" aria-selected="true">Users</button>
        <button class="nav-link" id="nav-bills-tab" data-bs-toggle="tab" data-bs-target="#nav-bills" type="button" role="tab" aria-controls="nav-bills" aria-selected="false">User groups & Roles</button>
        <button class="nav-link" id="nav-audit-tab" data-bs-toggle="tab" data-bs-target="#nav-audit" type="button" role="tab" aria-controls="nav-audit" aria-selected="false">System logs</button>
    </div>
</nav>

<div class="tab-content" id="nav-tabContent">
    <div class="tab-pane fade show active" id="nav-txns" role="tabpanel" aria-labelledby="nav-txns-tab">
        <?php require_once("admin_wrap_users.php"); ?>
    </div>

    <div class="tab-pane fade" id="nav-bills" role="tabpanel" aria-labelledby="nav-bills-tab">
        <?php require_once("admin_wrap_users_groups.php"); ?>
    </div>

    <div class="tab-pane fade" id="nav-audit" role="tabpanel" aria-labelledby="nav-audit-tab">
        <?php require_once("admin_wrap_audit_logs.php"); ?>
    </div>

</div>
