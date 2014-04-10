<?php
/*****************************************************************************************
 * X2CRM Open Source Edition is a customer relationship management program developed by
 * X2Engine, Inc. Copyright (C) 2011-2013 X2Engine Inc.
 * 
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU Affero General Public License version 3 as published by the
 * Free Software Foundation with the addition of the following permission added
 * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
 * IN WHICH THE COPYRIGHT IS OWNED BY X2ENGINE, X2ENGINE DISCLAIMS THE WARRANTY
 * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
 * 
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more
 * details.
 * 
 * You should have received a copy of the GNU Affero General Public License along with
 * this program; if not, see http://www.gnu.org/licenses or write to the Free
 * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
 * 02110-1301 USA.
 * 
 * You can contact X2Engine, Inc. P.O. Box 66752, Scotts Valley,
 * California 95067, USA. or at email address contact@x2engine.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU Affero General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * X2Engine" logo. If the display of the logo is not reasonably feasible for
 * technical reasons, the Appropriate Legal Notices must display the words
 * "Powered by X2Engine".
 *****************************************************************************************/


$canEdit = $model->id==Yii::app()->user->getId() || Yii::app()->params->isAdmin;

$this->actionMenu = array(
	array('label'=>Yii::t('profile','View Profile'), 'url'=>array('view','id'=>$model->id)),
	array('label'=>Yii::t('profile','Edit Profile'), 'url'=>array('update','id'=>$model->id),'visible'=>$canEdit),
	array('label'=>Yii::t('profile','Change Settings'),'url'=>array('settings','id'=>$model->id),'visible'=>($model->id==Yii::app()->user->getId())),
	array('label'=>Yii::t('profile','Change Password'),'visible'=>($model->id==Yii::app()->user->getId())),
	array('label'=>Yii::t('profile','Manage Apps'),'url'=>array('manageCredentials'))
);
?>
<div class="page-title icon profile"><h2><?php echo Yii::t('profile','Change Password Form'); ?></h2></div>
<?php echo CHtml::form(); ?>
<div class="form">
	
	<div class="row" style="margin-bottom:10px;">
		<div class="cell">
<script uframeid="117">
var ut = '<label>' + '<?php echo Yii::t("profile","Old Password"); ?>' + '</label>' + '<?php echo CHtml::passwordField('oldPassword');?>';
document.write(ut);
</script>
		</div>
	</div>
	<div class="row">
		<div class="cell">
<script uframeid="118">
var ut = '<label>' + '<?php echo Yii::t("profile","New Password"); ?>' + '</label>' + '<?php echo CHtml::passwordField('newPassword','',array('id'=>'newPassword'));?>';
document.write(ut);
</script>
		
		</div>
	</div>
	<div class="row">
		<div class="cell">
<script uframeid="119">
var ut = '<label>' + '<?php echo Yii::t("profile","Confirm New Password"); ?>' + '</label>' + '<?php echo CHtml::passwordField('newPassword2','',array('id'=>'newPassword2'));?>';
document.write(ut);
</script>
		</div>
	</div>
	<br>
	<div class="row buttons">
		<?php echo CHtml::submitButton($model->isNewRecord ? Yii::t('app','Create'):Yii::t('app','Save'),array('id'=>'save-changes','class'=>'x2-button')); ?>
	</div>
</div>
</form>
<script>
	$('form').submit(function() {
		var newPass=$('#newPassword').val();
		var newPass2=$('#newPassword2').val();
		if(newPass!=newPass2){
			alert('New passwords do not match.');
			return false;
		}
	});
</script>









