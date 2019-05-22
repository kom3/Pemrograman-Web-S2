<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "mahasiswa".
 *
 * @property string $nim NIM mahasiswa
 * @property string $nama Nama mahasiswa
 * @property string $alamat Alamat mahasiswa
 * @property string $kode_prodi Kode program studi mahasiswa
 *
 * @property Prodi $kodeProdi
 */
class Mahasiswa extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'mahasiswa';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['nim'], 'required'],
            [['nim'], 'string', 'max' => 10],
            [['nama'], 'string', 'max' => 100],
            [['alamat'], 'string', 'max' => 200],
            [['kode_prodi'], 'string', 'max' => 3],
            [['nim'], 'unique'],
            [['kode_prodi'], 'exist', 'skipOnError' => true, 'targetClass' => Prodi::className(), 'targetAttribute' => ['kode_prodi' => 'kode_prodi']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'nim' => 'NIM mahasiswa',
            'nama' => 'Nama mahasiswa',
            'alamat' => 'Alamat mahasiswa',
            'kode_prodi' => 'Kode program studi mahasiswa',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getKodeProdi()
    {
        return $this->hasOne(Prodi::className(), ['kode_prodi' => 'kode_prodi']);
    }
}
