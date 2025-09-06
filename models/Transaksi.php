<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "transaksi".
 *
 * @property int $id
 * @property int $pasien_id
 * @property int $total_harga
 * @property string $tanggal_transaksi
 *
 * @property Pasien $pasien
 * @property TransaksiObat[] $transaksiObats
 * @property TransaksiTindakan[] $transaksiTindakans
 */
class Transaksi extends \yii\db\ActiveRecord
{
    public $tindakanIds = [];
    public $obat1_id;
    public $obat2_id;
    public $obat3_id;
    public $jumlah1;
    public $jumlah2;
    public $jumlah3;
    public $tindakanPrices = [];
    public $obatPrices;

    /**
     * {@inheritdoc}
     */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        if ($insert) {
            foreach ($this->tindakanIds as $tindakanId) {
                $transaksiTindakan = new TransaksiTindakan();
                $transaksiTindakan->transaksi_id = $this->id;
                $transaksiTindakan->tindakan_id = $tindakanId;
                $transaksiTindakan->save();
            }
        }
        if ($insert) { // hanya dijalankan jika data baru disimpan
            // menyimpan data transaksi obat
            $transaksiObat = new TransaksiObat();
            $transaksiObat->transaksi_id = $this->id;
            $transaksiObat->obat_id = $this->obat1_id;
            $transaksiObat->jumlah = $this->jumlah1;
            $transaksiObat->save();

            // menyimpan data transaksi obat
            $transaksiObat = new TransaksiObat();
            $transaksiObat->transaksi_id = $this->id;
            $transaksiObat->obat_id = $this->obat2_id;
            $transaksiObat->jumlah = $this->jumlah2;
            $transaksiObat->save();

            // menyimpan data transaksi obat
            $transaksiObat = new TransaksiObat();
            $transaksiObat->transaksi_id = $this->id;
            $transaksiObat->obat_id = $this->obat3_id;
            $transaksiObat->jumlah = $this->jumlah3;
            $transaksiObat->save();

            $total_harga = 0;
            foreach ($this->transaksiTindakans as $transaksiTindakan) {
                $total_harga += $transaksiTindakan->tindakan->harga;
            }
            foreach ($this->transaksiObats as $transaksiObat) {
                $total_harga += $transaksiObat->obat->harga * $transaksiObat->jumlah;
            }
            $this->total_harga = $total_harga;
            $this->save();
        }
    }
    public static function tableName()
    {
        return 'transaksi';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['pasien_id'], 'required'],
            [['pasien_id'], 'integer'],
            [['tanggal_transaksi'], 'default', 'value' => date('Y-m-d')],
            [['obat1_id', 'obat2_id', 'obat3_id', 'jumlah1', 'jumlah2', 'jumlah3'], 'integer'],
            [['jumlah1', 'jumlah2', 'jumlah3'], 'default', 'value' => 1],
            [['tindakanIds'], 'required', 'message' => 'Pilih minimal satu tindakan.'],
            [['pasien_id'], 'exist', 'skipOnError' => true, 'targetClass' => Pasien::class, 'targetAttribute' => ['pasien_id' => 'id']],
        ];
    }


    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'pasien_id' => 'Pasien ID',
            'total_harga' => 'Total Harga',
            'tanggal_transaksi' => 'Tanggal Transaksi',
        ];
    }

    /**
     * Gets query for [[Pasien]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPasien()
    {
        return $this->hasOne(Pasien::class, ['id' => 'pasien_id']);
    }
    public function getObat1()
    {
        return $this->hasOne(Obat::className(), ['id' => 'obat1_id']);
    }

    // definisikan relasi obat2 pada model Transaksi
    public function getObat2()
    {
        return $this->hasOne(Obat::className(), ['id' => 'obat2_id']);
    }

    // definisikan relasi obat3 pada model Transaksi
    public function getObat3()
    {
        return $this->hasOne(Obat::className(), ['id' => 'obat3_id']);
    }

    // method untuk menghitung total harga tindakan
    public function hitungTindakanTotal()
    {
        $total = 0;
        foreach ($this->tindakanIds as $tindakanId) {
            $total += $this->tindakanPrices[$tindakanId];
        }
        return $total;
    }

    // method untuk menghitung total harga obat
    public function hitungObatTotal()
    {
        $total = 0;
        if ($this->obat1_id) {
            $total += $this->jumlah1 * $this->obat1->harga;
        }
        if ($this->obat2_id) {
            $total += $this->jumlah2 * $this->obat2->harga;
        }
        if ($this->obat3_id) {
            $total += $this->jumlah3 * $this->obat3->harga;
        }
        return $total;
    }

    // method untuk menghitung total harga
    public function getTotalHarga()
    {
        $total = 0;
        foreach ($this->transaksiTindakans as $transaksiTindakan) {
            $total += $transaksiTindakan->tindakan->harga;
        }
        foreach ($this->transaksiObats as $transaksiObat) {
            $total += $transaksiObat->obat->harga * $transaksiObat->jumlah;
        }
        return $total;
    }



    // ...


    /**
     * Gets query for [[TransaksiObats]].
     *
     * @return \yii\db\ActiveQuery
     */
    /**
     * Gets query for [[TransaksiTindakans]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTransaksiTindakans()
    {
        return $this->hasMany(TransaksiTindakan::class, ['transaksi_id' => 'id']);
    }
    public function getTransaksiObats()
    {
        return $this->hasMany(TransaksiObat::class, ['transaksi_id' => 'id']);
    }
    public function getObat()
    {
        return $this->hasOne(Obat::class, ['id' => 'obat_id']);
    }

    public function getTindakans()
    {
        return $this->hasMany(Tindakan::class, ['id' => 'tindakan_id'])
            ->viaTable('transaksi_tindakan', ['transaksi_id' => 'id']);
    }
}
