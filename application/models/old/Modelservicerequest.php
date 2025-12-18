<?php
    class Modelservicerequest extends CI_Model{
        
        function orderlab($env){
            $query =
                    "
                        SELECT A.PASIEN_ID, EPISODE_ID, TEST_ID, CATATAN, TRANS_CO,
                                --Patient Index
                                SR01_GET_SUFFIX(A.PASIEN_ID)PATIENTNAME,
                                (SELECT INT_PASIEN_ID FROM SR01_GEN_PASIEN_MS WHERE LOKASI_ID='001' AND AKTIF='1' AND PASIEN_ID=A.PASIEN_ID)PATIENTMR,
                                (SELECT SATUSEHAT_ID  FROM SR01_GEN_PASIEN_MS WHERE LOKASI_ID='001' AND AKTIF='1' AND PASIEN_ID=A.PASIEN_ID)PATIENTID,

                                --Practitioner Index
                                (SELECT IHS_ID      FROM SR01_GEN_USER_DATA WHERE LOKASI_ID='001' AND DOKTER_ID=A.CREATED_BY)PRACTITIONERID,
                                (SELECT UPPER(NAMA) FROM SR01_GEN_USER_DATA WHERE LOKASI_ID='001' AND DOKTER_ID=A.CREATED_BY)PRACTITIONERNAME,

                                (SELECT RESOURCE_ID FROM SR01_SATUSEHAT_BUNDLE WHERE LOKASI_ID='001' AND AKTIF='1' AND POLI_ID IS NOT NULL AND RESOURCE_TYPE='Encounter' AND ENVIRONMENT='".$env."' AND PASIEN_ID=A.PASIEN_ID AND EPISODE_ID=A.EPISODE_ID)RESOURCEID,
                                (SELECT POLI_ID     FROM SR01_SATUSEHAT_BUNDLE WHERE LOKASI_ID='001' AND AKTIF='1' AND POLI_ID IS NOT NULL AND RESOURCE_TYPE='Encounter' AND ENVIRONMENT='".$env."' AND PASIEN_ID=A.PASIEN_ID AND EPISODE_ID=A.EPISODE_ID)POLIID,
                                (SELECT LISTAGG(RESOURCE_ID, ',')WITHIN GROUP (ORDER BY RESOURCE_ID) FROM SR01_SATUSEHAT_BUNDLE WHERE LOKASI_ID='001' AND AKTIF='1' AND POLI_ID IS NOT NULL AND RESOURCE_TYPE='Condition' AND ENVIRONMENT='".$env."' AND PASIEN_ID=A.PASIEN_ID AND EPISODE_ID=A.EPISODE_ID)CONDITIONID,

                               (SELECT NAMA_LAYAN1 FROM SR01_KEU_LAYAN_MS WHERE LOKASI_ID='001' AND AKTIF='1' AND LAYAN_ID=A.TEST_ID)NAMAPELAYANAN,
                               TO_CHAR(A.CREATED_DATE - INTERVAL '7' HOUR,'YYYY-MM-DD')||'T'||TO_CHAR(CREATED_DATE - INTERVAL '7' HOUR,'HH24:MI:SS')||'+00:00' TGLORDER
                        FROM WEB_CO_LAB_DT A
                        WHERE A.SHOW_ITEM='1'
                        AND   A.TEST_ID IN ('LPK122','XELEKT000000010')
                        AND   A.EPISODE_ID NOT IN ('B124022194209','B124022193185','B124022201022','B124022202484','B124022195827','B124032211402','B124022198392','B124022205286')
                        AND   A.EPISODE_ID     IN (SELECT EPISODE_ID FROM SR01_SATUSEHAT_BUNDLE   WHERE LOKASI_ID='001' AND AKTIF='1' AND RESOURCE_TYPE='Encounter' AND ENVIRONMENT='".$env."' AND PASIEN_ID=A.PASIEN_ID AND EPISODE_ID=A.EPISODE_ID)
                        AND   A.TEST_ID    NOT IN (SELECT LAYAN_ID FROM SR01_SATUSEHAT_BUNDLE WHERE LOKASI_ID='001' AND AKTIF='1' AND POLI_ID IS NOT NULL AND RESOURCE_TYPE='ServiceRequest' AND JENIS='10' AND ENVIRONMENT='".$env."' AND PASIEN_ID=A.PASIEN_ID AND EPISODE_ID=A.EPISODE_ID AND LAYAN_ID=A.TEST_ID)
                        ORDER BY CREATED_DATE ASC
                        FETCH FIRST 10 ROWS ONLY
                    ";

			$recordset = $this->db->query($query);
			$recordset = $recordset->result();
			return $recordset;
        }

        function orderrad($env){
            $query =
                    "
                        SELECT X.*
                        FROM(
                        SELECT A.TRANS_CO, PASIEN_ID, EPISODE_ID, TEST_ID, CATATAN,
                        SR01_GET_SUFFIX(A.PASIEN_ID)PATIENTNAME,
                        (SELECT IHS_ID      FROM SR01_GEN_USER_DATA WHERE LOKASI_ID='001' AND DOKTER_ID=A.CREATED_BY)PRACTITIONERID,
                                (SELECT UPPER(NAMA) FROM SR01_GEN_USER_DATA WHERE LOKASI_ID='001' AND DOKTER_ID=A.CREATED_BY)PRACTITIONERNAME,
                                (SELECT INT_PASIEN_ID FROM SR01_GEN_PASIEN_MS WHERE LOKASI_ID='001' AND AKTIF='1' AND PASIEN_ID=A.PASIEN_ID)PATIENTMR,
                                (SELECT SATUSEHAT_ID  FROM SR01_GEN_PASIEN_MS WHERE LOKASI_ID='001' AND AKTIF='1' AND PASIEN_ID=A.PASIEN_ID)PATIENTID,
                                (SELECT POLI_ID     FROM SR01_SATUSEHAT_BUNDLE WHERE LOKASI_ID='001' AND AKTIF='1' AND POLI_ID IS NOT NULL AND RESOURCE_TYPE='Encounter' AND ENVIRONMENT='".$env."' AND PASIEN_ID=A.PASIEN_ID AND EPISODE_ID=A.EPISODE_ID)POLIID,
                        (SELECT TRANS_RAD FROM SR01_WORKLIST_RAD_DT WHERE TRANS_CO=A.TRANS_CO)TRANSRAD,
                        (SELECT RESOURCE_ID FROM SR01_SATUSEHAT_BUNDLE WHERE LOKASI_ID='001' AND AKTIF='1' AND POLI_ID IS NOT NULL AND RESOURCE_TYPE='Encounter' AND ENVIRONMENT='".$env."' AND PASIEN_ID=A.PASIEN_ID AND EPISODE_ID=A.EPISODE_ID)RESOURCEID,
                                (SELECT NAMA_LAYAN1 FROM SR01_KEU_LAYAN_MS WHERE LOKASI_ID='001' AND AKTIF='1' AND LAYAN_ID=A.TEST_ID)NAMAPELAYANAN,
                                TO_CHAR(A.CREATED_DATE - INTERVAL '7' HOUR,'YYYY-MM-DD')||'T'||TO_CHAR(CREATED_DATE - INTERVAL '7' HOUR,'HH24:MI:SS')||'+00:00' TGLORDER
                        FROM WEB_CO_RAD_DT A
                        WHERE A.SHOW_ITEM='1'
                        AND   A.TEST_ID='RAD010'
                        AND A.PASIEN_ID IN (
                            '00066573',
                            '00112026',
                            '00346868',
                            '00000819',
                            '00179231',
                            '00087319',
                            '00033849',
                            '00013614',
                            '00218697',
                            '00398018',
                            '00000615',
                            '00212880',
                            '00011281',
                            '00298418',
                            '00002309',
                            '00346852',
                            '00213229',
                            '00045728',
                            '00307162',
                            '00015183',
                            '00246010',
                            '00331194',
                            '00323099',
                            '00219197'
                        )

                        AND   A.CREATED_BY      = (SELECT DOKTER_ID  FROM SR01_GEN_USER_DATA WHERE LOKASI_ID='001' AND AKTIF='1' AND IHS_ID IS NOT NULL AND IHS_ID<>'NOT FOUND' AND DOKTER_ID=A.CREATED_BY)
                        AND   A.EPISODE_ID     IN (SELECT EPISODE_ID FROM SR01_SATUSEHAT_BUNDLE WHERE LOKASI_ID='001' AND AKTIF='1' AND POLI_ID IS NOT NULL AND RESOURCE_TYPE='Encounter' AND ENVIRONMENT='".$env."' AND PASIEN_ID=A.PASIEN_ID AND EPISODE_ID=A.EPISODE_ID)
                        AND   A.EPISODE_ID NOT IN (SELECT EPISODE_ID FROM SR01_SATUSEHAT_BUNDLE WHERE LOKASI_ID='001' AND AKTIF='1' AND RESOURCE_TYPE='ServiceRequest' AND JENIS='6' AND ENVIRONMENT='".$env."' AND PASIEN_ID=A.PASIEN_ID AND EPISODE_ID=A.EPISODE_ID )
                        ORDER BY CREATED_DATE ASC
                        )X
                        WHERE X.TRANSRAD IS NOT NULL
                        FETCH FIRST 10 ROWS ONLY
                    ";

			$recordset = $this->db->query($query);
			$recordset = $recordset->result();
			return $recordset;
        }
        
    }
?>
