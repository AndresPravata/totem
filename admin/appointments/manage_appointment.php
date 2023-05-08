<?php 
require_once('../../config.php');
if(isset($_GET['id']) && $_GET['id'] > 0){
    $qry = $conn->query("SELECT * from `appointments` where id = '{$_GET['id']}' ");
    if($qry->num_rows > 0){
        foreach($qry->fetch_assoc() as $k => $v){
            $$k=$v;
        }
    }
    $qry2 = $conn->query("SELECT * FROM `patient_meta` where patient_id = '{$patient_id}' ");
    foreach($qry2->fetch_all(MYSQLI_ASSOC) as $row){
        $patient[$row['meta_field']] = $row['meta_value'];
    }
}
?>
<style>
#uni_modal .modal-content>.modal-footer{
    display:none;
}
#uni_modal .modal-body{
    padding-top:0 !important;
}
</style>
<div class="container-fluid">
    <form id="appointment_form" class="py-2">
    <div class="row" id="appointment">
        <div class="col-6" id="frm-field">
            <input type="hidden" name="id" value="<?php echo isset($id) ? $id : '' ?>">
            <input type="hidden" name="patient_id" value="<?php echo isset($patient_id) ? $patient_id : '' ?>">
                <div class="form-group">
                    <label for="name" class="control-label">Nombre Completo</label>
                    <input type="text" class="form-control" id="name" name="name" value="<?php echo isset($patient['name']) ? $patient['name'] : '' ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="contact" class="control-label">Numero de telefono</label>
                    <input type="text" class="form-control" id="telefono" name="contact" value="<?php echo isset($patient['contact']) ? $patient['contact'] : '' ?>"  required>
                </div>
                <div class="form-group">
                    <label for="address" class="control-label">Dirección</label>
                    <textarea class="form-control" name="address" rows="3" required><?php echo isset($patient['address']) ? $patient['address'] : '' ?></textarea>
                </div>
                
        </div>
        <div class="col-6">
                
            <?php if($_settings->userdata('id') > 0): ?>

            
            
            <?php else: ?>
                <input type="hidden" name="ailment" value="">
            <?php endif; ?>
            <div class="form-group">
                <label for="date_sched" class="control-label">Fecha de turno</label>
                <input type="datetime-local" class="form-control" id="fecha" name="date_sched" value="<?php echo isset($date_sched)? date("Y-m-d\TH:i",strtotime($date_sched)) : "" ?>" required>
            </div>
            <?php if($_settings->userdata('id') > 0): ?>
            <div class="form-group">
                <label for="status" class="control-label">Tamaño del animal</label>
                <select name="status" id="status" class="custom custom-select">
                    <option value="0"<?php echo isset($status) && $status == "0" ? "selected": "" ?>>Chico</option>
                    <option value="1"<?php echo isset($status) && $status == "1" ? "selected": "" ?>>Mediano</option>
                    <option value="2"<?php echo isset($status) && $status == "2" ? "selected": "" ?>>Grande</option>
                </select>
            </div>
            <?php else: ?>
                <input type="hidden" name="status" value="0">
            <?php endif; ?>
        </div>
        <div class="form-group d-flex justify-content-end w-100 form-group">
           <!-- //<button class="btn-primary btn" href="file:///C:/xampp/htdocs/ImpresionTermica/index.html">Imprimir turno</button>//-->
        
           <button class="btn-primary btn" id="btnImprimir">Imprimir turno</button>
<script src="jquery-3.1.1.min.js"></script>
<script>
$(document).ready(function(){
    $('#btnImprimir').click(function(){
        $.ajax({
        type: "POST",
        data: { name: $('#name').val(), opcion: 1, telefono:$('#telefono').val(),fecha:$('#fecha').val() },
        url: "modeloAppoiment.php",
        success: function(respuesta) {
            respuesta = respuesta.trim();
            if (respuesta == 1) {
                
       $.ajax({
           url: 'impresionTermica/ticket.php',
           data:{ ID: respuesta},
           type: 'POST',
           success: function(response){
               if(response==1){
                   alert('Imprimiendo....');
               }else{
                   alert('Error');
               }
           }
       }); 
    } else {
                alert(":(", "No se pudo !", "error");
            }
        }
    });
    });
});

</script>


            <button class="btn-light btn ml-2" type="button" data-dismiss="modal">Cancelar</button>
        </div>
        </form>
    </div>
</div>
<script>
$(function(){
    $('#appointment_form').submit(function(e){
        e.preventDefault();
            var _this = $(this)
			 $('.err-msg').remove();
			start_loader();
			$.ajax({
				url:_base_url_+"classes/Master.php?f=save_appointment",
				data: new FormData($(this)[0]),
                cache: false,
                contentType: false,
                processData: false,
                method: 'POST',
                type: 'POST',
                dataType: 'json',
				error:err=>{
					console.log(err)
					alert_toast("Ocurrió un error",'error');
					end_loader();
				},
				success:function(resp){
					if(typeof resp =='object' && resp.status == 'success'){
                       location.reload()
					}else if(resp.status == 'failed' && !!resp.msg){
                        var el = $('<div>')
                            el.addClass("alert alert-danger err-msg").text(resp.msg)
                            _this.prepend(el)
                            el.show('slow')
                            $("html, body").animate({ scrollTop: $('#uni_modal').offset().top }, "fast");
                    }else{
						alert_toast("Ocurrió un error",'error');
                        console.log(resp)
					}
						end_loader();
				}
			})
    })
    $('#uni_modal').on('hidden.bs.modal', function (e) {
        if($('#appointment_form').length <= 0)
            location.reload()
    })
})
</script>