<?php
    class Modeilimagesstudy extends CI_Model{

        function dicom($env){
            $query =
                    "
                        SELECT A.PASIEN_ID, EPISODE_ID, POLI_ID, DOKTER_ID, TRANS_CO, LAYAN_ID, ACSN
                        FROM SR01_SATUSEHAT_TRANSAKSI A
                        WHERE A.LOKASI_ID='001'
                        AND   A.AKTIF='1'
                        AND   A.ENVIRONMENT='".$env."'
                        AND   A.RESOURCE_TYPE='ServiceRequest'
                        AND   A.JENIS='1'
                        AND   NOT EXISTS (SELECT 1 FROM SR01_SATUSEHAT_TRANSAKSI T WHERE T.LOKASI_ID='001' AND T.AKTIF='1' AND T.RESOURCE_TYPE='ImagingStudy' AND T.JENIS='1' AND T.ENVIRONMENT='".$env."' AND T.PASIEN_ID=A.PASIEN_ID AND T.EPISODE_ID=A.EPISODE_ID AND LAYAN_ID=A.LAYAN_ID)
                    ";

			$recordset = $this->db->query($query);
			$recordset = $recordset->result();
			return $recordset;
        }
    }
?>
