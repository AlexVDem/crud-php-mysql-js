
<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" type="text/css" href="w2ui/w2ui-1.5.min.css" />
    <script type="text/javascript" src="js/jquery-3.5.1.min.js"></script>
    <script type="text/javascript" src="w2ui/w2ui-1.5.min.js"></script>
</head>
<body>
    <div id="gridrecords" style="width: 100%; height: 500px;"></div>
    
</body>

<script type="text/javascript">

<?php include ('columns.php'); ?>
w2utils.settings.dataType = 'JSON'; //NEW! Put this JSON data type to declare new data format for 1.5 w2ui
w2utils.settings['dateFormat'] = <?php echo $dateformat;?>;
indexrows = <?php if ($useindexkeys){ echo $indexstrjson; }else{ echo 0;}?>;

var opriskgrid = $(function () {
    // define and render grid
    $(gridrecords).w2grid({
        name    : 'gridrecords',
        limit   : <?php echo $limit;?>, 
        //header  : "List",
        header  : <?php echo $tabletitle;?>,
        url: {
  			get: 'records.php',
   			save: 'records.php',
   			remove: 'records.php'
		},
        show: {
            header        : true,
            toolbar       : true,
            footer        : true,
            toolbarAdd    : true,
            toolbarDelete : true,
            toolbarSave   : true
        },
        toolbar: {
        items: [{ type: 'button', id: 'showChanges', text: 'Show Changes' }],
            onClick(event) {
                if (event.target == 'showChanges') {
                    showChanged()
                }
            }
        },

        columns: <?php echo $stringfields;?>,

        onLoad(event) {
            event.done(() => {
                rows = this.records;
                if (indexrows !== 0){
                    indexedarray = Object.entries(indexrows);
                    for (const [key, value] of indexedarray) {
                        for (var i = 0; i < rows.length; i++){
                            if (rows[i] != null){
                                rows[i][key] = value[rows[i][key]];//get records from indexed tables array
                            }
                        }
                    }
                    this.refresh();
                }
            });
        },


        onClick: function(event){
            
        },
        
        onAdd: function (event) {
           addRecord(0);
        },

        onChange: function(event) {
            
        },

        onSave: function (event) {
            
            event.done (() => {
                //console.log(event.changes);    

            });
        }
    });
});

window.showChanged = function() {
    const ind = w2ui['gridrecords'].get(3, true);
    const record = w2ui['gridrecords'].records[ind];
            
    w2popup.open({
        title: 'Records Changes',
        with: 600,
        height: 550,
        body: `<pre>${JSON.stringify(w2ui['gridrecords'].getChanges(), null, 4)}</pre>`,

        actions: { Ok: w2popup.close }
    });
}

function addRecord(recid) {

    let len = w2ui['gridrecords'].records.length
    w2ui['gridrecords'].add( { recid: len + 1} )
    newrecord = {cmd:'newempty', recid: len+1, name :'record_edit', record :{}};
    $.ajax({
        type : "POST",
        url  : "records.php",
        data: JSON.stringify(newrecord),
        dataType: 'json',
        success: function(result){
            //move to new record
            w2ui['gridrecords'].resize();
            w2ui['gridrecords'].reload();
            setTimeout(function () { // allow to render first
                pos = w2ui['gridrecords'].get(result.count, true);
                w2ui['gridrecords'].scrollIntoView(result.count,0,true);
            }, 300);
            
        }
    });
}

</script>