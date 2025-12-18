<?php
    class ModelObservation extends CI_Model{

        // function AnamnesaawalRJ($env){
        //     $query =
        //             "
        //                 SELECT Y.*
        //                 FROM(
        //                     SELECT ROWNUM NOURUT, X.*,
        //                             (SELECT SATUSEHAT_ID  FROM SR01_SATUSEHAT_LOCATION WHERE LOKASI_ID='001' AND AKTIF='1' AND STATUS='active' AND VALUE=X.POLIID)LOCATIONID,
        //                             (SELECT NAME          FROM SR01_SATUSEHAT_LOCATION WHERE LOKASI_ID='001' AND AKTIF='1' AND STATUS='active' AND VALUE=X.POLIID)LOCATIONNAME                        
        //                     FROM(
        //                         SELECT A.TRANS_ID, PASIEN_ID, EPISODE_ID, TV_TEKANAN_DARAH, TV_TEKANAN_DARAH2, TV_FREK_NADI, TV_SUHU, TV_FREK_NAFAS, ANT_BB, ANT_TB, ANT_IMT,
        //                                 (SELECT DISTINCT TO_CHAR(CREATED_DATE- INTERVAL '7' HOUR,'YYYY-MM-DD')||'T'||TO_CHAR(CREATED_DATE- INTERVAL '7' HOUR,'HH24:MI:SS')||'+00:00' FROM SR01_MED_PRWT_TR WHERE LOKASI_ID='001' AND AKTIF='1' AND PASIEN_ID=A.PASIEN_ID AND EPISODE_ID=A.EPISODE_ID)TRIAGE,
        //                                 (SELECT IHS_ID      FROM SR01_GEN_USER_DATA WHERE LOKASI_ID='001' AND AKTIF='1' AND 'SIRS01_'||UPPER(USER_ID)=UPPER(A.CREATED_BY))PRACTITIONERID,
        //                                 (SELECT UPPER(NAMA) FROM SR01_GEN_USER_DATA WHERE LOKASI_ID='001' AND AKTIF='1' AND 'SIRS01_'||UPPER(USER_ID)=UPPER(A.CREATED_BY))PRACTITIONERNAME,
                            
        //                                 SR01_GET_SUFFIX(A.PASIEN_ID)PATIENTNAME,
        //                                 (SELECT INT_PASIEN_ID FROM SR01_GEN_PASIEN_MS WHERE LOKASI_ID='001' AND AKTIF='1' AND PASIEN_ID=A.PASIEN_ID)PATIENTMR,
        //                                 (SELECT SATUSEHAT_ID  FROM SR01_GEN_PASIEN_MS WHERE LOKASI_ID='001' AND AKTIF='1' AND PASIEN_ID=A.PASIEN_ID)PATIENTID,

        //                                 (SELECT RESOURCE_ID FROM SR01_SATUSEHAT_BUNDLE WHERE LOKASI_ID='001' AND AKTIF='1' AND POLI_ID IS NOT NULL AND RESOURCE_TYPE='Encounter' AND ENVIRONMENT='".$env."' AND PASIEN_ID=A.PASIEN_ID AND EPISODE_ID=A.EPISODE_ID)RESOURCEID,
        //                                 (SELECT POLI_ID     FROM SR01_SATUSEHAT_BUNDLE WHERE LOKASI_ID='001' AND AKTIF='1' AND POLI_ID IS NOT NULL AND RESOURCE_TYPE='Encounter' AND ENVIRONMENT='".$env."' AND PASIEN_ID=A.PASIEN_ID AND EPISODE_ID=A.EPISODE_ID)POLIID
                                        
        //                         FROM SR01_MED_ANAMAWAL A
        //                         WHERE A.LOKASI_ID='001'
        //                         AND   A.AKTIF='1'
        //                         AND   A.EPISODE_ID IN (SELECT EPISODE_ID FROM SR01_SATUSEHAT_BUNDLE WHERE LOKASI_ID='001' AND AKTIF='1' AND POLI_ID IS NOT NULL AND RESOURCE_TYPE='Encounter' AND ENVIRONMENT='".$env."' AND PASIEN_ID=A.PASIEN_ID AND EPISODE_ID=A.EPISODE_ID)
        //                         AND   A.EPISODE_ID NOT IN (SELECT EPISODE_ID FROM SR01_SATUSEHAT_BUNDLE WHERE LOKASI_ID='001' AND AKTIF='1' AND POLI_ID IS NOT NULL AND RESOURCE_TYPE='Observation' AND JENIS='1' AND ENVIRONMENT='".$env."' AND PASIEN_ID=A.PASIEN_ID AND EPISODE_ID=A.EPISODE_ID)
        //                         AND   A.CREATED_BY   =(SELECT 'SIRS01_'||UPPER(USER_ID) FROM SR01_GEN_USER_DATA WHERE LOKASI_ID='001' AND AKTIF='1' AND IHS_ID IS NOT NULL AND IHS_ID<>'NOT FOUND' AND 'SIRS01_'||UPPER(USER_ID)=UPPER(A.CREATED_BY))
        //                         AND   A.CREATED_DATE =(SELECT DISTINCT MAX(CREATED_DATE) FROM SR01_MED_ANAMAWAL WHERE LOKASI_ID='001' AND AKTIF='1' AND PASIEN_ID=A.PASIEN_ID AND EPISODE_ID=A.EPISODE_ID)

        //                         ORDER BY CREATED_DATE ASC
        //                     )X
        //                 )Y
        //                 WHERE Y.NOURUT <= 1
        //             ";

		// 	$recordset = $this->db->query($query);
		// 	$recordset = $recordset->result();
		// 	return $recordset;
        // }

        function hasillab($env){
            $query =
                    "
                        SELECT X.*,
                            SR01_GET_SUFFIX(X.PASIEN_ID)PATIENTNAME,
                            (SELECT INT_PASIEN_ID FROM SR01_GEN_PASIEN_MS WHERE LOKASI_ID='001' AND AKTIF='1' AND PASIEN_ID=X.PASIEN_ID)PATIENTMR,
                            (SELECT SATUSEHAT_ID  FROM SR01_GEN_PASIEN_MS WHERE LOKASI_ID='001' AND AKTIF='1' AND PASIEN_ID=X.PASIEN_ID)PATIENTID,

                            (SELECT RESOURCE_ID FROM SR01_SATUSEHAT_BUNDLE WHERE LOKASI_ID='001' AND AKTIF='1' AND POLI_ID IS NOT NULL AND RESOURCE_TYPE='Encounter' AND ENVIRONMENT='".$env."' AND PASIEN_ID=X.PASIEN_ID AND EPISODE_ID=X.EPISODE_ID)RESOURCEID,
                            (SELECT RESOURCE_ID FROM SR01_SATUSEHAT_BUNDLE WHERE LOKASI_ID='001' AND AKTIF='1' AND POLI_ID IS NOT NULL AND RESOURCE_TYPE='ServiceRequest' AND ENVIRONMENT='".$env."' AND PASIEN_ID=X.PASIEN_ID AND EPISODE_ID=X.EPISODE_ID)SERVICERQUESTID,
                            (SELECT RESOURCE_ID FROM SR01_SATUSEHAT_BUNDLE WHERE LOKASI_ID='001' AND AKTIF='1' AND POLI_ID IS NOT NULL AND RESOURCE_TYPE='Specimen' AND ENVIRONMENT='".$env."' AND PASIEN_ID=X.PASIEN_ID AND EPISODE_ID=X.EPISODE_ID)SPECIMENID
                        FROM(
                            SELECT A.TEST_ID, TES_ORDER_ID, RESULT_VALUE, SAMPEL_ID, RESULT_FLAG,
                                (SELECT PASIEN_ID  FROM SR01_WORKLIST_LAB WHERE SAMPEL_ID=A.SAMPEL_ID)PASIEN_ID,
                                (SELECT EPISODE_ID FROM SR01_WORKLIST_LAB WHERE SAMPEL_ID=A.SAMPEL_ID)EPISODE_ID,

                                TO_CHAR(A.LAST_UPDATED_DATE - INTERVAL '7' HOUR,'YYYY-MM-DD')||'T'||TO_CHAR(LAST_UPDATED_DATE - INTERVAL '7' HOUR,'HH24:MI:SS')||'+00:00' TGLHASIL
                                
                            FROM DT_TES_ORDER A
                            WHERE A.DONE_STATUS='04'
                            AND   A.TEST_ID   IN ('CL','K','NA')
                            AND   A.SAMPEL_ID IN (SELECT SAMPEL_ID FROM SR01_SATUSEHAT_BUNDLE   WHERE LOKASI_ID='001' AND AKTIF='1' AND RESOURCE_TYPE='Specimen' AND JENIS='11' AND ENVIRONMENT='".$env."' AND SAMPEL_ID=A.SAMPEL_ID)
                            AND   A.SAMPEL_ID NOT IN (SELECT SAMPEL_ID FROM SR01_SATUSEHAT_BUNDLE   WHERE LOKASI_ID='001' AND AKTIF='1' AND RESOURCE_TYPE='Observation' AND JENIS='12' AND ENVIRONMENT='".$env."' AND SAMPEL_ID=A.SAMPEL_ID AND LAYAN_ID=A.TEST_ID)
                            ORDER BY CREATED_DATE ASC
                            FETCH FIRST 10 ROWS ONLY
                        )X
                    ";

			$recordset = $this->db->query($query);
			$recordset = $recordset->result();
			return $recordset;
        }
    }
?>
