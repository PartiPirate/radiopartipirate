/* global $ */

function modifyLinkHandler(e) {
    e.preventDefault();
    e.stopPropagation();
        
    var link = $(this).attr("href");
    link += "&ajax=true";
    
    $.get(link, null, function(data) {
        $("#track-div").html(data);
    }, "html");
}

function saveTrackHandler(e) {
    e.preventDefault();
    e.stopPropagation();
    
    var form = $(this).parents("form");
    var action = form.attr("action");
    
    $.post(action, form.serialize(), function(data) {
        $("#track-div").html("");
        
        // update data
        var table = $('#tracks').DataTable();
        table.ajax.reload();
    }, "text");
}

$(function() {
    $('#tracks tfoot th').each( function () {
        var title = $(this).text();
        $(this).html( '<input type="text" placeholder="Chercher dans '+title+'" />' );
    } );
 
    // DataTable
    var table = $('#tracks').DataTable({
        "order": [[6, "asc"], [0, "asc"]],
        "language": {
            "decimal":        ",",
            "emptyTable":     "Aucune piste disponible",
            "info":           "De _START_ à _END_ des _TOTAL_ pistes",
            "infoEmpty":      "De 0 à 0 des 0 pistes",
            "infoFiltered":   "(filtrées à partir des _MAX_ pistes)",
            "infoPostFix":    "",
            "thousands":      " ",
            "lengthMenu":     "Montrer _MENU_ pistes",
            "loadingRecords": "Chargement...",
            "processing":     "Traitement...",
            "search":         "Recherche:",
            "zeroRecords":    "Aucune piste trouvée",
            "paginate": {
                "first":      "Première",
                "last":       "Dernière",
                "next":       "Suivante",
                "previous":   "Précédente"
            },
            "aria": {
                "sortAscending":  ": activer pour ordonner la colonne de manière ascendante",
                "sortDescending": ": activer pour ordonner la colonne de manière descendante"
            }
        },
        "ajax": {
            url: "api.php?method=do_getTracks",
            dataSrc: 'tracks'
        },
        "columns": [
            { data: 'tra_title' },
            { data: 'tra_author' },
            { data: 'tra_album' },
            { data: 'tra_genres' },
            { data: 'tra_duration_time', className: "text-right" },
            { data: 'tra_last_broadcast', className: "text-right" },
            { data: 'tra_number_of_broadcasts', className: "text-right" },
            { data: null, 
                render: function ( data, type, row, meta ) {
                    return '<a href="track.php?id=' + data["tra_id"] + '" class="modify-link">Éditer</a> <a href="do_deleteTrack.php?id=' + data["tra_id"] + '">Supprimer</a>';
                } 
            }
    ]
    });
 
    // Apply the search
    table.columns().every( function () {
        var that = this;
 
        $( 'input', this.footer() ).on( 'keyup change', function () {
            if ( that.search() !== this.value ) {
                that
                    .search( this.value )
                    .draw();
            }
        } );
    } );    
    
    $("#tracks").on("click", ".modify-link", modifyLinkHandler);
    $("#track-div").on("click", "#save-track-button", saveTrackHandler);
});