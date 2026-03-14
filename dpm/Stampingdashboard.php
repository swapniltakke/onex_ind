<?php
session_start();
$user = $_SESSION['username'];
$pass = $_SESSION['pass'];
include('shared/CommonManager.php');
include('header.php');
include('menustamping.php');
$login_type = $_SESSION['role_name'];
if ($login_type == "Stamping") {
    $role_name = "Stamping";
} else if ($login_type == "Manufacturing") {
    $role_name = "Stamping";
}
$sql = "SELECT 
            COUNT(*) AS total_count
        FROM 
        (
            SELECT 
                t1.barcode, 
                GROUP_CONCAT(DISTINCT t1.username) AS other_usernames
            FROM 
                tbl_transactions_user_details t1
                JOIN tbl_transactions t2 ON t1.barcode = t2.barcode
            WHERE 
                t1.role_name = :role_name 
                AND t1.stage_id = :stage_id 
                AND t1.STATUS = :status
                AND t2.STATUS = :status
            GROUP BY 
                t1.barcode
            ORDER BY 
                t1.id DESC
        ) AS subquery";
$res = DbManager::fetchPDOQueryData('spectra_db', $sql, [":role_name" => "$role_name", ":stage_id" => "8", ":status" => "0"])["data"];
$cnt = $res[0]['total_count'];
$login_type = $_SESSION['role_name'];
if ($login_type == "Stamping") {
    $welcome_msg =  "Greetings, Stamper! Welcome to Digital Production Management";
} else if ($login_type == "Manufacturing") {
    $welcome_msg =  "Greetings, Manufacturer! Welcome to Digital Production Management";
}
?>
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header mb-2">
        <div class="container-fluid">
            <div class="pagehead">
                <div class="row">
                    <div class=" col-md-6 col-sm-6">
                        <h5><?php echo $welcome_msg; ?></h5>
                    </div>
                    <div class="col-sm-8">
                        <div class="tab float-sm-right">
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <br><br>
        <?php if ($cnt > 0) { ?>
            <div class="alert text-center blink-red font-size-increase" role="alert">
                <a href="stampingdetails.php" class="blink-red font-size-increase" style="text-decoration: none; font-weight: bold;">
                    <p>
                        You have <strong><?php echo $cnt; ?></strong> stamping pending.
                        Please click on <a href="stamping_pending_list.php" style="color: #1491EB; font-weight: bold; text-decoration: underline;">this link to review them</a>.
                    </p>
                </a>
            </div>
        <?php } else { ?>
            <div class="alert text-center blink-green font-size-increase" role="alert" style="font-weight: bold;">
                Congratulations! There are no stamping pending.
            </div>
        <?php } ?>
        <!-- /.container-fluid -->
    </section>
</div>
<!-- /.content-wrapper -->
<?php include('footer.php'); ?>
<script>
$(function() {
    var Accordion = function(el, multiple) {
        this.el = el || {};
        this.multiple = multiple || false;

        // Variables privadas
        var links = this.el.find('.link');
        var links1 = this.el.find('.link1');
        // Evento
        links.on('click', {
            el: this.el,
            multiple: this.multiple
        }, this.dropdown)
        links1.on('click', {
            el: this.el,
            multiple: this.multiple
        }, this.dropdown1)
    }

    Accordion.prototype.dropdown = function(e) {
        var $el = e.data.el;
        $this = $(this),
            $next = $this.next();

        $next.slideToggle();
        $this.parent().toggleClass('open');

        if (!e.data.multiple) {
            $el.find('.submenu').not($next).slideUp().parent().removeClass('open');
        };
    }

    Accordion.prototype.dropdown1 = function(e) {
        var $el = e.data.el;
        $this = $(this),
            $next = $this.next();

        $next.slideToggle();
        $this.parent().toggleClass('open');

        if (!e.data.multiple) {
            $el.find('.submenu1').not($next).slideUp().parent().removeClass('open');
        };
    }

    var accordion = new Accordion($('#accordion'), false);
});
</script>
<script>
$(document).ready(function() {
    $(".icon-input-btn").each(function() {
        var btnFont = $(this).find(".btn").css("font-size");
        var btnColor = $(this).find(".btn").css("color");
        $(this).find(".fa").css({
            'font-size': btnFont,
            'color': btnColor
        });
    });

    // Blink effect for the alert
    function blink() {
        $(".blink-red.font-size-increase").css({
            "color": "#EF0137",
            "font-size": "1.2em"
        }).fadeOut(500).fadeIn(500, function() {
            $(this).css({
                "color": "#EF0137",
                "font-size": "1.2em"
            });
            blink();
        });
        $(".blink-green.font-size-increase").css({
            "color": "#01893A",
            "font-size": "1.2em"
        });
    }
    blink();
});
</script>
</body>
</html>