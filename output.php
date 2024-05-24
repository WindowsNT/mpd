<link
  rel="stylesheet"
  href="https://cdn.jsdelivr.net/npm/bulma@1.0.0/css/bulma.min.css"
>
<script src="https://cdn.jsdelivr.net/npm/@vizuaalog/bulmajs@0.12.2/dist/bulma.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js" integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/bs4/dt-1.10.18/fh-3.1.4/r-2.2.2/datatables.min.css"/>
<script type="text/javascript" src="https://cdn.datatables.net/v/bs4/dt-1.10.18/fh-3.1.4/r-2.2.2/datatables.min.js"></script>

<script>

function datataboff()
{
var dt = $('.datatable');
if (dt)
    dt.dataTable().fnDestroy()

}

var datatabe = 0;
function datatab(resp = true,fi = false)
{
           if (datatabe == 1)
                return;
            datatabe = 1;
          var dt = $('.datatable');
			if (dt)
		    	{
			    dt.dataTable({
			        dom: 'Bfrt',
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

$(document).ready(function()
{
  AutoBu();
  datatab();
});

</script>