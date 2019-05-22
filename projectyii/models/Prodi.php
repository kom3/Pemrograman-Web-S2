<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "prodi".
 *
 * @property string $kode_prodi Kode program studi
 * @property string $nama_prodi Nama program studi
 * @property string $alamat_prodi Alamat kantor program studi
 * @property string $nip_kaprodi NIP kepala program studi
 *
 * @property Mahasiswa[] $mahasiswas
 */
class Prodi extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'prodi';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['kode_prodi'], 'required'],
            [['kode_prodi'], 'string', 'max' => 3],
            [['nama_prodi'], 'string', 'max' => 50],
            [['alamat_prodi'], 'string', 'max' => 100],
            [['nip_kaprodi'], 'string', 'max' => 20],
            [['kode_prodi'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'kode_prodi' => 'Kode program studi',
            'nama_prodi' => 'Nama program studi',
            'alamat_prodi' => 'Alamat kantor program studi',
            'nip_kaprodi' => 'NIP kepala program studi',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMahasiswas()
    {
        return $this->hasMany(Mahasiswa::className(), ['kode_prodi' => 'kode_prodi']);
    }
}
