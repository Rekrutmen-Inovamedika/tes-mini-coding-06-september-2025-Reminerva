<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use app\models\Pasien;
use app\models\Obat;
use app\models\Tindakan;

/** @var yii\web\View $this */
/** @var app\models\Transaksi $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="transaksi-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'pasien_id')->dropDownList(
        ArrayHelper::map(Pasien::find()->all(), 'id', 'nama'),
        ['prompt' => 'Pilih Pasien']
    ) ?>

    <?= $form->field($model, 'tindakanIds')->checkboxList(
        ArrayHelper::map(Tindakan::find()->all(), 'id', 'nama_tindakan')
    ) ?>

    <?php for ($i = 1; $i <= 3; $i++): ?>
        <?= $form->field($model, "obat{$i}_id")->dropDownList(
            Obat::getList(),
            ['prompt' => "Pilih Obat {$i}"]
        ) ?>
        <?= $form->field($model, "jumlah{$i}")->textInput([
            'type' => 'number',
            'value' => 1,
            'min' => 1
        ]) ?>
    <?php endfor; ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>

<?php
// JS untuk update total harga secara live
$this->registerJs('
    var tindakanPrice = ' . json_encode($model->tindakanPrices) . ';
    var obatPrice = ' . json_encode($model->obatPrices) . ';
    var totalPriceField = $("#' . Html::getInputId($model, 'total_harga') . '");

    function updateTotalPrice() {
        var tindakanTotal = 0;
        $("input[name=\'Transaksi[tindakanIds][]\']:checked").each(function() {
            var tindakanId = $(this).val();
            if (tindakanPrice.hasOwnProperty(tindakanId)) {
                tindakanTotal += tindakanPrice[tindakanId];
            }
        });

        var obatTotal = 0;
        for (var i = 1; i <= 3; i++) {
            var obatId = $("#transaksi-obat" + i + "_id").val();
            var jumlah = parseInt($("#transaksi-jumlah" + i).val()) || 0;
            var price = obatPrice[obatId] || 0;
            obatTotal += price * jumlah;
        }

        var totalPrice = tindakanTotal + obatTotal;
        totalPriceField.val(totalPrice);
        $("#total-price-display").text(totalPrice);
    }

    $("input[name=\'Transaksi[tindakanIds][]\']").change(updateTotalPrice);
    for (var i = 1; i <= 3; i++) {
        $("#transaksi-obat" + i + "_id").change(updateTotalPrice);
        $("#transaksi-jumlah" + i).change(updateTotalPrice);
    }

    updateTotalPrice();
');
?>
