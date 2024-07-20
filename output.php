<link
  rel="stylesheet"
  href="https://cdn.jsdelivr.net/npm/bulma@1.0.0/css/bulma.min.css"
>

<title>Μητρώo Προσόντων και Διαγωνισμών</title>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<script src="https://cdn.jsdelivr.net/npm/@vizuaalog/bulmajs@0.12.2/dist/bulma.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js" integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>


<link href="https://cdn.datatables.net/v/bm/jszip-3.10.1/dt-2.0.8/af-2.7.0/b-3.0.2/b-colvis-3.0.2/b-html5-3.0.2/b-print-3.0.2/date-1.5.2/fc-5.0.1/fh-4.0.1/kt-2.12.1/r-3.0.2/sc-2.4.3/sb-1.7.1/sp-2.3.1/sl-2.0.3/sr-1.4.1/datatables.min.css" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/v/bm/jszip-3.10.1/dt-2.0.8/af-2.7.0/b-3.0.2/b-colvis-3.0.2/b-html5-3.0.2/b-print-3.0.2/date-1.5.2/fc-5.0.1/fh-4.0.1/kt-2.12.1/r-3.0.2/sc-2.4.3/sb-1.7.1/sp-2.3.1/sl-2.0.3/sr-1.4.1/datatables.min.js"></script>


<!-- include summernote css/js -->
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>

<script>

function datataboff()
{
var dt = $('.datatable');
if (dt)
    dt.dataTable().fnDestroy()

}

function gotop(url)
{
  window.location = url;
}

var datatabe = 0;
var datatab_nob = true;

function datatab(resp = true,fi = false)
{
           if (datatabe == 1)
                return;
            datatabe = 1;
          var dt = $('.datatable');
			if (dt)
		    	{
			    dt.dataTable({
            layout: {
              topStart: {
              buttons: ['copy', 'csv', 'excel', 'pdf', 'print']
              }
            },
			        dom: datatab_nob ? 'frt' : 'frtB',
			        paging: false,
			        bInfo: false,
    					fixedHeader: true,
			        responsive: resp,
              fixedHeader: fi,
		    			xScroll: true,
              bFilter: true,
			        aaSorting: []
  			    });
          }
}


function gotop(q)
{
     window.location = q;
}


function sure(tit,msg,url)
{
  Bulma().alert({
    type: 'danger',
    title: tit,
    body: msg,
    confirm: {
        label: 'Ναι!',
        onClick: function() {
          gotop(url);
        },
    },
    cancel: 'Όχι'
});
}

function goblank(q)
{
  var win = window.open(q, '_blank');
  win.focus();
  }

function AutoBu()
{
  
  $(".sureautobutton").click(function()
    {
      var url = $(this).attr("href");
      var txt = $(this).attr("q");
      if (txt == "" || typeof txt == "undefined")  
        txt = "Σίγουρα;";
      sure("Επιβεβαίωση",txt,url);
    });

   $(".autobutton").click(
        function(event)
            {
            var e = $(this).attr("exist");
            if (e == "exist")
                {
                event.preventDefault();
                return;
                }
            var nv = $(this).html();
            var nv2 = '<span class="fa fa-circle-o-notch fa-spin" aria-hidden="true"></span> ' + nv;
            $(this).html(nv2);

            var url = $(this).attr("href");
            var rep = 0;
            if (url == "" || url === undefined)
               {
rep = 1;
 url = $(this).attr("hrefr");
}
            if (url == "" || url === undefined)
                {
                // Form?
                var form = $(this).parents('form:first');
                if(form !== undefined)
                    {
                    $(this).attr("exist","exist");
//                    $('input.btn-primary').prop("disabled", "disabled");
                    //$(this).prop('disabled',true);
                    // block();
                    elblock($(this));
                    form.submit();
                    }

                return;
                }

            $(this).attr("exist","exist");
            $(this).prop('disabled',true);
            var trg = $(this).attr("trg");

            if (trg == "self")
                g(url);
             else
                {
if (rep == 1)


window.location.replace(url);
else
                gotop(url);
            }
            }
        );
}


// $s .= sprintf('<button class="block button is-small is-danger" href="check.php?t=%s&reject=%s">Απόρριψη</button>',$rolerow['ID'],$r1['ID']);

function rejectproson(role,prid)
{
  var v = prompt('Λόγος απόρριψης:');
  if (v == null)
    return;

  var url = 'check.php?t=' + role + '&reject=' + prid + '&reason=' + v;
  window.location = url;
}

$(document).ready(function()
{
  AutoBu();
  datatab();
});

</script>