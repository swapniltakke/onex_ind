<style>
    #langChoiceIcons {
        position: absolute;
        left: 8px;
        top: 17px;
        transition: 2s !important;
    }

    #langChoiceIcons img {
        width: 35px;
        padding-right: 3px;
        filter: opacity(30%);
    }

    #langChoiceIcons img.active {
        filter: drop-shadow(8px 0px 17px gray);
    }
</style>
<div style="display: flex;float: left; padding-top: 10px;" id="langChoiceIcons">
    <?php include_once($_SERVER["DOCUMENT_ROOT"] . '/shared/language/language-button-content.php') ?>
</div>
<script src="/shared/language/language-converter.js?<?= time()?>"></script>
<script src="/shared/language/language-button.js"></script>