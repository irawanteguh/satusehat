<?php
    class Modelquestionnaireresponse extends CI_Model{

        function poliklinik($env){
            $query =
                    "
                        SELECT A.PASIEN_ID, EPISODE_ID, POLI_ID, DOKTER_ID, RESOURCE_ID,
                                --Patient Index
                                SR01_GET_SUFFIX(A.PASIEN_ID)PATIENTNAME,
                                (SELECT INT_PASIEN_ID FROM SR01_GEN_PASIEN_MS WHERE LOKASI_ID='001' AND AKTIF='1' AND PASIEN_ID=A.PASIEN_ID)PATIENTMR,
                                (SELECT SATUSEHAT_ID  FROM SR01_GEN_PASIEN_MS WHERE LOKASI_ID='001' AND AKTIF='1' AND PASIEN_ID=A.PASIEN_ID)PATIENTID,

                                --Practitioner Index
                                (SELECT IHS_ID      FROM SR01_GEN_USER_DATA WHERE LOKASI_ID='001' AND AKTIF='1' AND DOKTER_ID=A.DOKTER_ID)PRACTITIONERID,
                                (SELECT UPPER(NAMA) FROM SR01_GEN_USER_DATA WHERE LOKASI_ID='001' AND AKTIF='1' AND DOKTER_ID=A.DOKTER_ID)PRACTITIONERNAME,

                                (SELECT TV_SKOR_NYERI FROM SR01_MED_ANAMAWAL WHERE LOKASI_ID='001' AND AKTIF='1' AND PASIEN_ID=A.PASIEN_ID AND EPISODE_ID=A.EPISODE_ID AND CREATED_DATE=(SELECT MAX(CREATED_DATE) FROM SR01_MED_ANAMAWAL WHERE LOKASI_ID='001' AND AKTIF='1' AND PASIEN_ID=A.PASIEN_ID AND EPISODE_ID=A.EPISODE_ID))SKORNYERI,
                                (SELECT TO_CHAR(CREATED_DATE - INTERVAL '7' HOUR,'YYYY-MM-DD')||'T'||TO_CHAR(CREATED_DATE - INTERVAL '7' HOUR,'HH24:MI:SS')||'+00:00' FROM SR01_MED_ANAMAWAL WHERE LOKASI_ID='001' AND AKTIF='1' AND PASIEN_ID=A.PASIEN_ID AND EPISODE_ID=A.EPISODE_ID AND CREATED_DATE=(SELECT MAX(CREATED_DATE) FROM SR01_MED_ANAMAWAL WHERE LOKASI_ID='001' AND AKTIF='1' AND PASIEN_ID=A.PASIEN_ID AND EPISODE_ID=A.EPISODE_ID))CREATEDDATE

                        FROM SR01_SATUSEHAT_TRANSAKSI A
                        WHERE A.LOKASI_ID='001'
                        AND   A.AKTIF='1'
                        AND   A.RESOURCE_TYPE='Encounter'
                        AND   A.JENIS='1'
                        AND   A.ENVIRONMENT='".$env."'
                        AND   NOT EXISTS (SELECT 1 FROM SR01_SATUSEHAT_TRANSAKSI WHERE LOKASI_ID='001' AND AKTIF='1' AND RESOURCE_TYPE='QuestionnaireResponse' AND JENIS='1' AND ENVIRONMENT='".$env."' AND PASIEN_ID=A.PASIEN_ID AND EPISODE_ID=A.EPISODE_ID)
                        FETCH FIRST 1 ROWS ONLY
                    ";

			$recordset = $this->db->query($query);
			$recordset = $recordset->result();
			return $recordset;
        }
        
        
    }
?>
