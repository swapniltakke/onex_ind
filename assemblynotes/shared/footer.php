<div class="footer">
    <div class="float-right">
        <strong>OneX</strong> <?php echo $footer_display = isset($footer_display) ? $footer_display : 'Missing Assembly Items'; ?>
    </div>
    <div>
        <strong>Copyright</strong> Siemens <?= SharedManager::getFromSharedEnv("FACTORY_ORG_CODE"); ?> &copy; <?php echo date('Y') ?>
    </div>
</div>