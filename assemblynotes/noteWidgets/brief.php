<?php include_once '../core/index.php' ?>

<div class="row">
    <div class="col-lg-4 col-md-4 col-sm-12">
        <div class="widget style1 navy-bg" data-toggle="tooltip" data-placement="bottom" title="Güncelleme tarihi esas alınarak hesaplanmıştır">
            <div class="row vertical-align">
                <div class="col-3">
                    <i class="fa fa-circle fa-3x"></i>
                </div>
                <div class="col-9 text-right">
                    <span data-translate="closed-note-pointer">Kapalı Not Göstergesi</span>

                    <h3 id="closeNoteWidget" class="font-bold mt-1"></h3>
                    <h3 id="closeReworkTimeWidget" class="font-bold"></h3>

                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4 col-md-4 col-sm-12">
        <div class="widget style1 yellow-bg" data-toggle="tooltip" data-placement="bottom" title="Kayıt tarihi esas alınarak hesaplanmıştır">
            <div class="row vertical-align">
                <div class="col-3">
                    <i class="fa fa-circle-o fa-3x"></i>
                </div>
                <div class="col-9 text-right">
                    <span data-translate="open-note-pointer">Açık Not Göstergesi</span>
                    <h3 id="openNoteWidget" class="font-bold mt-3"></h3>
                    <h3 id="openReworkTimeWidget" class="font-bold mt-2"></h3>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4 col-md-4 col-sm-12">
        <div class="widget style1 blue-bg">
            <div class="row vertical-align">
                <div class="col-3">
                    <i class="fa fa-cubes fa-3x"></i>
                </div>
                <div class="col-9 text-right">
                    <span data-translate="total-note-pointer">Toplam Not Göstergesi</span>

                    <h3 id="totalNoteWidget" class="font-bold mt-1"></h3>
                    <h3 id="totalReworkTimeWidget" class="font-bold"></h3>

                </div>
            </div>
        </div>
    </div>
</div>

