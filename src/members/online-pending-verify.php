<?php
/*******************************************************************************
 *
 *  website     : http://www.ecclesiacrm.com
 *  copyright   : Copyright 2017
 *
 ******************************************************************************/

require '../Include/Config.php';
require '../Include/Functions.php';

//Set the page title
$sPageTitle = gettext('Pending Self Verify');
require '../Include/Header.php';

use EcclesiaCRM\dto\SystemURLs;

?>

<div class="row">
    <div class="col-lg-12">
        <div class="box">
            <div class="box-header">
                <h3 class="box-title"><?= _("Families") ?></h3>
            </div>
            <div class="box-body">
                <table id="families" class="table table-striped table-bordered table-responsive data-table">
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>


<script nonce="<?= SystemURLs::getCSPNonce() ?>">
    $(document).ready(function () {
        $("#families").DataTable({
            "language": {
                "url": window.CRM.plugin.dataTable.language.url
            },
            ajax: {
                url: window.CRM.root + "/api/families/pending-self-verify",
                dataSrc: 'families'
            },
            "dom": window.CRM.plugin.dataTable.dom,
            "tableTools": {
                "sSwfPath": window.CRM.plugin.dataTable.tableTools.sSwfPath
            },
            responsive: true,
            columns: [
                {
                    title: i18next.t('Family Id'),
                    data: 'FamilyId',
                    searchable: false,
                    render: function (data, type, full, meta) {
                        return '<a href=' + window.CRM.root + '/FamilyView.php?FamilyID=' + data + '>' + data + '</a>';
                    }
                },
                {
                    title: i18next.t('Family'),
                    data: 'FamilyName',
                    searchable: true
                },
                {
                    title: i18next.t('Valid Until'),
                    data: 'ValidUntilDate',
                    searchable: false,
                    render: function (data, type, full, meta) {
                        return moment(data).format("MM-DD-YY");
                    }
                }
            ],
            order: [[2, "desc"]]
        });
    });
</script>
<?php
require '../Include/Footer.php';
?>
