<?php if($done){ ?>
    <div class="alert alert-success rounded-10 p-4 text-center">
        <p class="py-2">عملیات با موفقیت انجام شد</p>
        <a class="btn btn-primary btn-block" href="<?= base_url('wallet') ?>">ادامه</a>
    </div>
<?php }else{ ?>
    <div class="alert alert-danger rounded-10 p-4 text-center">
        <p class="py-2">عملیات با شکست مواجه شد</p>
        <a class="btn btn-primary btn-block" href="<?= base_url('add_wallet_value') ?>">ادامه</a>
    </div>
<?php } ?>