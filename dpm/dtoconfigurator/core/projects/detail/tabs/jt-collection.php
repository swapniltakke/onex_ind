<link href="/dpm/dtoconfigurator/assets/css/projects/detail/tabs/jt-collection.css" rel="stylesheet" type="text/css"/>

<div class="ui active inline indeterminate text loader" style="display:none;!important;"><b>Loading JT Page</b></div>

<div id="jtCollectionContainer" class="ui container-fluid">
    <h3 class="ui header" style="margin-top:1rem;">
        <i class="dropbox icon"></i>
        <div class="content">
            JT Collection
            <div class="sub header">This section allows you to collect JT files.</div>
        </div>
    </h3>

    <div id="jtCollectionMessage" class="ui yellow compact message" style="margin-bottom:1.5rem;">
        <i class="info circle icon"></i> Please select <b>list type</b> to see JT cards in this section.
    </div>

    <div id="jtCollectionInfo" class="ui info message" style="">
        <i class="info circle icon"></i>
        You can <b>filter by typicals or panels</b> by selecting filter items in nachbau filter section above.<br>
        You can download a <b>maximum of 4 JT files</b> at once.
    </div>

    <div id="jtCollectionFilterHeader"></div>
    <div id="jtCollectionCards" class="ui grid centered"></div>
</div>

<script src="/dpm/dtoconfigurator/assets/js/projects/detail/tabs/jt-collection.js?<?=uniqid()?>"></script>
