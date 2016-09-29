<?php
/* This file is part of Jeedom.
 *
 * Jeedom is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Jeedom is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
 */

require_once dirname(__FILE__) . '/../../../core/php/core.inc.php';
include_file('core', 'authentification', 'php');
if (!isConnect()) {
    include_file('desktop', '404', 'php');
    die();
}
?>
		
<form class="form-horizontal">
<fieldset>
<div class="form-group">
    <label class="col-lg-2 control-label">{{URL de retour}}</label>
    <div class="alert alert-warning col-lg-4">
        <span><?php echo network::getNetworkAccess('external') . '/plugins/xee/core/php/callback.php';?></span>
    </div>
</div>
<div class="form-group">
    <label class="col-lg-2 control-label">{{Client ID : }}</label>
    <div class="col-lg-2">
		<input class="configKey form-control xeeClientID" data-l1key="clientid" style="margin-top:-5px" placeholder="Client ID API"/>
    </div>
	<label class="col-lg-2 control-label">{{Client Secret : }}</label>
    <div class="col-lg-2">
		<input class="configKey form-control" data-l1key="clientsecret" style="margin-top:-5px" placeholder="Client Secret Api"/>
    </div>
</div>
 <div class="form-group">
 <label class="col-lg-2 control-label">{{Lier Compte : }}</label>
            <div class="col-lg-1">
                <a class="btn btn-warning" id="bt_linkAccount"><i class='fa fa-share'></i> {{Lier}}</a>
            </div>
			<div class="col-lg-1">
                <a class="btn btn-info" id="bt_refreshAccount"><i class='fa fa-retweet'></i> {{Rafraîchir}}</a>
            </div>
            <div class="alert alert-info col-lg-8">
                {{Bien sauver avant. Nécessite de pouvoir accéder à jeedom de l'exterieur et d'avoir bien configuré la partie reseaux dans jeedom}}
            </div>
        </div>
</fieldset> 
</form>
<script>
 $('#bt_linkAccount').on('click', function () {
	location.href = "https://cloud.xee.com/v3/auth/auth?client_id="+$('.xeeClientID').value();
});
 $('#bt_refreshAccount').on('click', function () {
	 bootbox.confirm('{{Cela lancera un rafraîchissement complet, voulez-vous continuer ? }}', function (result) {
      if (result) {
		$.ajax({// fonction permettant de faire de l'ajax
            type: "POST", // methode de transmission des données au fichier php
            url: "plugins/xee/core/ajax/xee.ajax.php", // url du fichier php
            data: {
                action: "refresh",
            },
            dataType: 'json',
            error: function (request, status, error) {
                handleAjaxError(request, status, error);
            },
            success: function (data) { // si l'appel a bien fonctionné
                if (data.state != 'ok') {
                    $('#div_alert').showAlert({message: data.result, level: 'danger'});
                    return;
                }
                $('#div_alert').showAlert({message: '{{Réussie}}', level: 'success'});
            }
        });
    };
	});
});
</script>