<?php ?>
<style>
    #langChoiceIcons img {
        width: 35px;
        padding-right: 3px;
        filter: opacity(30%);
    }

    #langChoiceIcons img.active {
        filter: drop-shadow(8px 0px 17px gray);
    }
</style>
<div id="langChoiceIcons" style="display: flex;">
    <a id="en" class="lang" style="padding-right: 2px;" href="#"><img width="25px" height="25px"
                                                                      src="/images/uk.svg" alt="English"></a>
    <a id="tr" class="lang" style="padding-right: 2px;" href="#"><img width="25px" height="25px" class="active"
                                                                      src="/images/turkey.svg" alt="Turkish"
        ></a>
    <!--    <a id="de" class="lang" href="#"><img width="25px" height="25px" src="/images/germany.svg"-->
    <!--                                          alt="German"></a>-->
</div>
