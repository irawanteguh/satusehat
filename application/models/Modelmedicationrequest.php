<?php
    class Modelmedicationrequest extends CI_Model{

        function medicationsingledose($env){
            $query =
                    "
                        SELECT X.*,
                               (SELECT NAME           FROM SR01_SATUSEHAT_POA_MS WHERE LOKASI_ID='001' AND KFA_ID=X.KFAID)POANAME
                        FROM(
                            SELECT A.PASIEN_ID, EPISODE_ID, TRANS_CO, OBAT_ID,
                                (SELECT KFA_ID FROM SR01_FRM_OBAT_MS WHERE LOKASI_ID='001' AND AKTIF='1' AND OBAT_ID=A.OBAT_ID)KFAID,
                                (SELECT POLI_ID     FROM SR01_SATUSEHAT_TRANSAKSI WHERE LOKASI_ID='001' AND AKTIF='1' AND RESOURCE_TYPE='Encounter' AND ENVIRONMENT='".$env."' AND PASIEN_ID=A.PASIEN_ID AND EPISODE_ID=A.EPISODE_ID)POLI_ID,
                                (SELECT DOKTER_ID   FROM SR01_SATUSEHAT_TRANSAKSI WHERE LOKASI_ID='001' AND AKTIF='1' AND RESOURCE_TYPE='Encounter' AND ENVIRONMENT='".$env."' AND PASIEN_ID=A.PASIEN_ID AND EPISODE_ID=A.EPISODE_ID)DOKTER_ID

                            FROM WEB_CO_RESEP_DT A
                            WHERE A.SHOW_ITEM='1'
                            AND   A.TYPE='00'
                            AND   A.EPISODE_ID NOT IN ('B125102873325','B125102874460','B125102876151')
                            AND   A.OBAT_ID NOT IN ('VAKSI0000000012','VAKSI0000000013')
                            AND   EXISTS (SELECT 1 FROM SR01_FRM_OBAT_MS WHERE OBAT_ID=A.OBAT_ID AND KFA_ID IS NOT NULL)
                            AND   EXISTS (SELECT 1 FROM SR01_SATUSEHAT_TRANSAKSI T WHERE T.LOKASI_ID='001' AND T.AKTIF='1' AND T.RESOURCE_TYPE='Encounter' AND T.JENIS='1' AND T.ENVIRONMENT='".$env."' AND T.PASIEN_ID=A.PASIEN_ID AND T.EPISODE_ID=A.EPISODE_ID)
                            AND   NOT EXISTS (SELECT 1 FROM SR01_SATUSEHAT_TRANSAKSI T WHERE T.LOKASI_ID='001' AND T.AKTIF='1' AND T.RESOURCE_TYPE='Medication' AND T.JENIS='1' AND T.ENVIRONMENT='".$env."' AND T.PASIEN_ID=A.PASIEN_ID AND T.EPISODE_ID=A.EPISODE_ID AND TRANS_CO=A.TRANS_CO AND OBAT_ID=A.OBAT_ID)
                            FETCH FIRST 10 ROWS ONLY
                        )X
                    ";

			$recordset = $this->db->query($query);
			$recordset = $recordset->result();
			return $recordset;
        }

    }
?>


