<?php
$staffName = AdminAuth::getStaffName();
$staffEmail = AdminAuth::getStaffEmail();
$staffGroupName = AdminAuth::getStaffGroupName();
$staffLocationId = AdminLocation::getId();
$staffAvatar = md5(strtolower(trim($staffEmail)));
$staffLocations = AdminLocation::listLocations()->all();
$staffEditLink = admin_url('staffs/edit/'.AdminAuth::staff()->getKey());
$logoutLink = admin_url('logout');
?>
<li class="nav-item dropdown">
    <a href="#" class="nav-link" data-toggle="dropdown">
        <?php if ($staffLocationId) { ?>
            <span class="icon-cover rounded-circle bg-info">
                <i class="fa fa-map-marker text-white"></i>
            </span>
        <?php } else { ?>
            <img class="rounded-circle" src="<?= '//www.gravatar.com/avatar/'.$staffAvatar.'.png?s=64&d=mm'; ?>">
        <?php } ?>
    </a>
    <div class="dropdown-menu">
        <div class="d-flex flex-column w-100 align-items-center">
            <div class="pt-4 px-4 pb-2">
                <img class="rounded-circle" src="<?= '//www.gravatar.com/avatar/'.$staffAvatar.'.png?s=64&d=mm'; ?>">
            </div>
            <div class="pb-4 text-center">
                <div class="text-uppercase"><?= $staffName; ?></div>
                <div class="text-muted"><?= $staffGroupName; ?></div>
            </div>
        </div>
        <div class="px-3 pb-4">
            <form method="POST" accept-charset="UTF-8">
                <div class="input-group">
                    <div class="input-group-prepend">
                        <div class="input-group-text<?= $staffLocationId ? ' text-info' : '' ?>">
                            <i class="fa fa-map-marker fa-fw"></i>
                        </div>
                    </div>
                    <select
                        name="location"
                        class="form-control"
                        data-request="<?= $this->getEventHandler('onChooseLocation') ?>"
                    >
                        <option value="0"><?= e(lang('admin::lang.text_select_location')) ?></option>
                        <?php foreach ($staffLocations as $key => $value) { ?>
                            <option
                                value="<?= $key ?>"
                                <?= $key == $staffLocationId ? 'selected="selected"' : '' ?>
                            ><?= $value ?></option>
                        <?php } ?>
                    </select>
                </div>
            </form>
        </div>
        <a class="dropdown-item" href="<?= $staffEditLink; ?>">
            <i class="fa fa-user fa-fw"></i><?= lang('admin::lang.text_edit_details'); ?>
        </a>
        <a class="dropdown-item text-danger" href="<?= $logoutLink; ?>">
            <i class="fa fa-power-off fa-fw"></i><?= lang('admin::lang.text_logout'); ?>
        </a>
        <div role="separator" class="dropdown-divider"></div>
        <a class="dropdown-item text-muted" href="https://tastyigniter.com/about/" target="_blank">
            <i class="fa fa-info-circle fa-fw"></i><?= lang('admin::lang.text_about_tastyigniter'); ?>
        </a>
        <a class="dropdown-item text-muted" href="https://docs.tastyigniter.com" target="_blank">
            <i class="fa fa-book fa-fw"></i><?= lang('admin::lang.text_documentation'); ?>
        </a>
        <a class="dropdown-item text-muted" href="https://forum.tastyigniter.com" target="_blank">
            <i class="fa fa-users fa-fw"></i><?= lang('admin::lang.text_community_support'); ?>
        </a>
    </div>
</li>
