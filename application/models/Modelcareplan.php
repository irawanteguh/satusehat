<?php
    class Modelcareplan extends CI_Model{

        function careplan($env){
            $query =
                    "
                        SELECT A.PASIEN_ID, EPISODE_ID, TRANS_SOAP, P,
                               TO_CHAR(A.CREATED_DATE-INTERVAL '7' HOUR,'YYYY-MM-DD')||'T'||TO_CHAR(A.CREATED_DATE-INTERVAL '7' HOUR,'HH24:MI:SS')||'+00:00' CREATED_DATE,

                               (SELECT RESOURCE_ID FROM SR01_SATUSEHAT_TRANSAKSI WHERE LOKASI_ID='001' AND AKTIF='1' AND RESOURCE_TYPE='Encounter' AND ENVIRONMENT='".$env."' AND PASIEN_ID=A.PASIEN_ID AND EPISODE_ID=A.EPISODE_ID)RESOURCEID,
                               (SELECT POLI_ID     FROM SR01_SATUSEHAT_TRANSAKSI WHERE LOKASI_ID='001' AND AKTIF='1' AND RESOURCE_TYPE='Encounter' AND ENVIRONMENT='".$env."' AND PASIEN_ID=A.PASIEN_ID AND EPISODE_ID=A.EPISODE_ID)POLI_ID,
                               (SELECT DOKTER_ID   FROM SR01_SATUSEHAT_TRANSAKSI WHERE LOKASI_ID='001' AND AKTIF='1' AND RESOURCE_TYPE='Encounter' AND ENVIRONMENT='".$env."' AND PASIEN_ID=A.PASIEN_ID AND EPISODE_ID=A.EPISODE_ID)DOKTER_ID,

                               --Patient Index
                                SR01_GET_SUFFIX(A.PASIEN_ID)PATIENTNAME,
                                (SELECT INT_PASIEN_ID FROM SR01_GEN_PASIEN_MS WHERE LOKASI_ID='001' AND AKTIF='1' AND PASIEN_ID=A.PASIEN_ID)PATIENTMR,
                                (SELECT SATUSEHAT_ID  FROM SR01_GEN_PASIEN_MS WHERE LOKASI_ID='001' AND AKTIF='1' AND PASIEN_ID=A.PASIEN_ID)PATIENTID,

                               --Practitioner Index
                                (SELECT IHS_ID      FROM SR01_GEN_USER_DATA WHERE LOKASI_ID='001' AND DOKTER_ID=A.CREATED_BY)PRACTITIONERID,
                                (SELECT UPPER(NAMA) FROM SR01_GEN_USER_DATA WHERE LOKASI_ID='001' AND DOKTER_ID=A.CREATED_BY)PRACTITIONERNAME

                        FROM WEB_CO_DIAGNOSA_DT A
                        WHERE A.LOKASI_ID='001'
                        AND   A.SHOW_ITEM='1'
                        AND   EXISTS (SELECT 1 FROM SR01_SATUSEHAT_TRANSAKSI T WHERE T.LOKASI_ID='001' AND T.AKTIF='1' AND T.RESOURCE_TYPE='Encounter' AND T.JENIS='1' AND T.ENVIRONMENT='".$env."' AND T.PASIEN_ID=A.PASIEN_ID AND T.EPISODE_ID=A.EPISODE_ID)
                        AND   NOT EXISTS (SELECT 1 FROM SR01_SATUSEHAT_TRANSAKSI T WHERE T.LOKASI_ID='001' AND T.AKTIF='1' AND T.RESOURCE_TYPE='CarePlan' AND T.JENIS='1' AND T.ENVIRONMENT='".$env."' AND T.PASIEN_ID=A.PASIEN_ID AND T.EPISODE_ID=A.EPISODE_ID)
                        FETCH FIRST 10 ROWS ONLY
                    ";

			$recordset = $this->db->query($query);
			$recordset = $recordset->result();
			return $recordset;
        }
    }
?>
