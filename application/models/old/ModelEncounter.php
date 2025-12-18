<?php
    class ModelEncounter extends CI_Model{

        function EncounterRJ($env){
            $query =
                    "
                        SELECT Y.*
                        FROM(
                            SELECT ROWNUM NOURUT, X.*
                            FROM(
                                SELECT A.POLI_ID, PASIEN_ID, EPISODE_ID, TO_CHAR(A.TGL_MASUK, 'DL' )TGLKUNJUNGAN,
                                    CASE
                                        WHEN A.POLI_ID='POLI0000000035' THEN
                                        (SELECT ID FROM SR01_SATUSEHAT_EOC WHERE LOKASI_ID='001' AND AKTIF='1' AND STATUS='active' AND TYPE='cancer' AND SATUSEHAT_ID=(SELECT SATUSEHAT_ID  FROM SR01_GEN_PASIEN_MS WHERE LOKASI_ID='001' AND AKTIF='1' AND PASIEN_ID=A.PASIEN_ID))
                                        ELSE
                                        'X'
                                    END EOCID,

                                    --Location
                                    (SELECT SATUSEHAT_ID  FROM SR01_SATUSEHAT_LOCATION WHERE LOKASI_ID='001' AND AKTIF='1' AND STATUS='active' AND VALUE=A.POLI_ID)LOCATIONID,
                                    (SELECT NAME          FROM SR01_SATUSEHAT_LOCATION WHERE LOKASI_ID='001' AND AKTIF='1' AND STATUS='active' AND VALUE=A.POLI_ID)LOCATIONNAME,
                                                                                                                                            
                                    --Patient Index
                                    SR01_GET_SUFFIX(A.PASIEN_ID)PATIENTNAME,
                                    (SELECT NO_IDENTITAS FROM SR01_GEN_PASIEN_MS WHERE LOKASI_ID='001' AND AKTIF='1' AND PASIEN_ID=A.PASIEN_ID)NO_IDENTITAS,
                                    (SELECT INT_PASIEN_ID FROM SR01_GEN_PASIEN_MS WHERE LOKASI_ID='001' AND AKTIF='1' AND PASIEN_ID=A.PASIEN_ID)PATIENTMR,
                                    (SELECT SATUSEHAT_ID  FROM SR01_GEN_PASIEN_MS WHERE LOKASI_ID='001' AND AKTIF='1' AND PASIEN_ID=A.PASIEN_ID)PATIENTID,
                                                                                                                                            
                                    --Practitioner Index
                                    (SELECT IHS_ID      FROM SR01_GEN_USER_DATA WHERE LOKASI_ID='001' AND AKTIF='1' AND DOKTER_ID=A.DOKTER_ID)PRACTITIONERID,
                                    (SELECT UPPER(NAMA) FROM SR01_GEN_USER_DATA WHERE LOKASI_ID='001' AND AKTIF='1' AND DOKTER_ID=A.DOKTER_ID)PRACTITIONERNAME,
                                                                                                                                            
                                    --Status History
                                    (SELECT DISTINCT TO_CHAR(CREATED_DATE- INTERVAL '7' HOUR,'YYYY-MM-DD')||'T'||TO_CHAR(CREATED_DATE- INTERVAL '7' HOUR,'HH24:MI:SS')||'+00:00' FROM WEB_CO_REGISTRASI_ONLINE_HD WHERE LOKASI_ID='001' AND AKTIF='1' AND PASIEN_ID=A.PASIEN_ID AND EPISODE_ID=A.EPISODE_ID)PLANNED,
                                    (SELECT DISTINCT TO_CHAR(TGL_HADIR- INTERVAL '7' HOUR,'YYYY-MM-DD')||'T'||TO_CHAR(TGL_HADIR- INTERVAL '7' HOUR,'HH24:MI:SS')||'+00:00'       FROM WEB_CO_REGISTRASI_ONLINE_HD WHERE LOKASI_ID='001' AND AKTIF='1' AND PASIEN_ID=A.PASIEN_ID AND EPISODE_ID=A.EPISODE_ID )PERIODSTART,
                                    (SELECT DISTINCT TO_CHAR(CREATED_DATE- INTERVAL '7' HOUR,'YYYY-MM-DD')||'T'||TO_CHAR(CREATED_DATE- INTERVAL '7' HOUR,'HH24:MI:SS')||'+00:00' FROM SR01_MED_PRWT_TR WHERE LOKASI_ID='001' AND AKTIF='1' AND PASIEN_ID=A.PASIEN_ID AND EPISODE_ID=A.EPISODE_ID)TRIAGE,
                                    (SELECT DISTINCT TO_CHAR(CREATED_DATE- INTERVAL '7' HOUR,'YYYY-MM-DD')||'T'||TO_CHAR(CREATED_DATE- INTERVAL '7' HOUR,'HH24:MI:SS')||'+00:00' FROM WEB_CO_MULAI_PERIKSA     WHERE SHOW_ITEM='1' AND CREATED_DATE=(SELECT MIN(CREATED_DATE) FROM WEB_CO_MULAI_PERIKSA   WHERE SHOW_ITEM='1' AND PASIEN_ID=A.PASIEN_ID AND EPISODE_ID=A.EPISODE_ID)AND PASIEN_ID=A.PASIEN_ID AND EPISODE_ID=A.EPISODE_ID)INPROGRESSSTART,
                                    (SELECT DISTINCT TO_CHAR(CREATED_DATE- INTERVAL '7' HOUR,'YYYY-MM-DD')||'T'||TO_CHAR(CREATED_DATE- INTERVAL '7' HOUR,'HH24:MI:SS')||'+00:00' FROM WEB_CO_SELESAI_PERIKSA   WHERE SHOW_ITEM='1' AND CREATED_DATE=(SELECT MAX(CREATED_DATE) FROM WEB_CO_SELESAI_PERIKSA WHERE SHOW_ITEM='1' AND PASIEN_ID=A.PASIEN_ID AND EPISODE_ID=A.EPISODE_ID)AND PASIEN_ID=A.PASIEN_ID AND EPISODE_ID=A.EPISODE_ID)INPROGRESSEND,
                                    
                                    CASE
                                        WHEN TGL_KELUAR < TRUNC(LAST_UPDATED_DATE) THEN
                                        TO_CHAR(LAST_UPDATED_DATE - INTERVAL '7' HOUR,'YYYY-MM-DD')||'T'||TO_CHAR(LAST_UPDATED_DATE - INTERVAL '7' HOUR,'HH24:MI:SS')||'+00:00'
                                        ELSE
                                        TO_CHAR(TGL_KELUAR - INTERVAL '7' HOUR,'YYYY-MM-DD')||'T'||TO_CHAR(TGL_KELUAR - INTERVAL '7' HOUR,'HH24:MI:SS')||'+00:00'
                                    END FINISH,
                                                                                                                                    
                                    (SELECT DISTINCT KONTROL        FROM WEB_CO_RESUME WHERE SHOW_ITEM='1' AND PASIEN_ID=A.PASIEN_ID AND EPISODE_ID=A.EPISODE_ID AND CREATED_DATE=(SELECT MAX(CREATED_DATE) FROM WEB_CO_RESUME WHERE SHOW_ITEM='1' AND PASIEN_ID=A.PASIEN_ID AND EPISODE_ID=A.EPISODE_ID))KONTROL,
                                    (SELECT DISTINCT RENCANA_TL     FROM WEB_CO_RESUME WHERE SHOW_ITEM='1' AND PASIEN_ID=A.PASIEN_ID AND EPISODE_ID=A.EPISODE_ID AND CREATED_DATE=(SELECT MAX(CREATED_DATE) FROM WEB_CO_RESUME WHERE SHOW_ITEM='1' AND PASIEN_ID=A.PASIEN_ID AND EPISODE_ID=A.EPISODE_ID))RENCANA_TL,                                    
                                    (SELECT DISTINCT KONDISI_PULANG FROM WEB_CO_RESUME WHERE SHOW_ITEM='1' AND PASIEN_ID=A.PASIEN_ID AND EPISODE_ID=A.EPISODE_ID AND CREATED_DATE=(SELECT MAX(CREATED_DATE) FROM WEB_CO_RESUME WHERE SHOW_ITEM='1' AND PASIEN_ID=A.PASIEN_ID AND EPISODE_ID=A.EPISODE_ID))KONDISIPULANG
                        
                                FROM SR01_KEU_EPISODE A
                                WHERE A.LOKASI_ID='001'
                                AND   A.AKTIF='1'
                                AND   A.JENIS_EPISODE='O'
                                AND   A.STATUS_EPISODE='55'
                                AND   A.TGL_KELUAR IS NOT NULL
                                AND   A.EPISODE_ID='B125072797212'
                                AND   A.POLI_ID    NOT IN ('UGD01','UGD02','MEDIC0000000000','POLI0000000039','HEMOD0000000000')
                                AND   A.EPISODE_ID NOT IN (SELECT TRANS_ID FROM SR01_SATUSEHAT_ISSUE_LOG WHERE RESOURCE_TYPE='OperationOutcome' AND EXPRESSION='Condition.code[0].code' AND TRANS_ID=A.EPISODE_ID )
                                AND   A.EPISODE_ID NOT IN (SELECT EPISODE_ID FROM SR01_SATUSEHAT_BUNDLE   WHERE LOKASI_ID='001' AND AKTIF='1' AND RESOURCE_TYPE='Encounter' AND ENVIRONMENT='".$env."' AND PASIEN_ID=A.PASIEN_ID AND EPISODE_ID=A.EPISODE_ID)
                                AND   A.EPISODE_ID     IN (SELECT EPISODE_ID FROM SR01_RM_RESUME_ICD10 WHERE LOKASI_ID='001' AND AKTIF='1' AND JENIS IN ('1','2') AND ICD10_ID LIKE 'D%' AND PASIEN_ID=A.PASIEN_ID AND EPISODE_ID=A.EPISODE_ID )
                                AND   A.POLI_ID    = (SELECT VALUE FROM SR01_SATUSEHAT_LOCATION WHERE LOKASI_ID='001' AND AKTIF='1' AND STATUS='active' AND VALUE=A.POLI_ID)
                                AND   A.DOKTER_ID  = (SELECT DOKTER_ID FROM SR01_GEN_USER_DATA WHERE LOKASI_ID='001' AND AKTIF='1' AND IHS_ID IS NOT NULL AND IHS_ID<>'NOT FOUND' AND DOKTER_ID=A.DOKTER_ID)
                                AND   A.EPISODE_ID = (SELECT EPISODE_ID FROM WEB_CO_REGISTRASI_ONLINE_HD WHERE LOKASI_ID='001' AND AKTIF='1' AND TGL_HADIR IS NOT NULL AND PASIEN_ID=A.PASIEN_ID AND EPISODE_ID=A.EPISODE_ID)
                                AND   A.EPISODE_ID = (SELECT EPISODE_ID FROM SR01_RESUME_MEDIS WHERE LOKASI_ID='001' AND AKTIF IN ('1','2') AND APPROVE_DR='Y' AND IS_CASEMIX='Y' AND PASIEN_ID=A.PASIEN_ID AND EPISODE_ID=A.EPISODE_ID )
                                AND   A.EPISODE_ID = (SELECT EPISODE_ID FROM SR01_DOCUMENT WHERE LOKASI_ID='001' AND AKTIF='1' AND JNS_DOC='RMD' AND PASIEN_ID=A.PASIEN_ID AND EPISODE_ID=A.EPISODE_ID) 
                                AND   A.PASIEN_ID  = (SELECT PASIEN_ID FROM SR01_GEN_PASIEN_MS WHERE LOKASI_ID='001' AND AKTIF='1' AND ACC_SATUSEHAT='Y' AND SATUSEHAT_ID IS NOT NULL AND ACC_SATUSEHAT_FASKES IS NOT NULL AND PASIEN_ID=A.PASIEN_ID)
                                AND   TRUNC(A.TGL_MASUK) >= ( SELECT TRUNC(SATUSEHAT_DATE) FROM SR01_GEN_PASIEN_MS WHERE LOKASI_ID='001' AND AKTIF='1' AND ACC_SATUSEHAT='Y' AND SATUSEHAT_ID IS NOT NULL AND PASIEN_ID=A.PASIEN_ID)
                                ORDER BY TGL_MASUK DESC
                            )X
                            WHERE X.EOCID IS NOT NULL
                        )Y
                        WHERE Y.NOURUT <= 1
                    ";

			$recordset = $this->db->query($query);
			$recordset = $recordset->result();
			return $recordset;
        }

        function condition($episodeid){
            $query =
                    "
                        SELECT Y.*
                        FROM(
                            SELECT X.*, ROWNUM RANK
                            FROM(              
                                SELECT A.DIAGNOSA, 
                                    ( SELECT KODE_ICD FROM SR01_MED_ICD10_MS WHERE SHOW_ITEM='1' AND KODE=A.ICD10_ID )KODEDIAGNOSA
                                FROM SR01_RM_RESUME_ICD10 A
                                WHERE A.LOKASI_ID='001'
                                AND   A.AKTIF='1'
                                AND   A.JENIS IN ('1','2')
                                AND   A.ICD10_ID LIKE 'D%'
                                AND   A.ICD10_ID NOT IN ('D08614')
                                AND   A.EPISODE_ID='".$episodeid."'
                                ORDER BY JENIS, DIAGNOSA
                            )X
                        )Y
                    ";

			$recordset = $this->db->query($query);
			$recordset = $recordset->result();
			return $recordset;
        }

        function updatedatapatient($pasienid, $data){           
            $sql =   $this->db->update("SR01_GEN_PASIEN_MS",$data,array("PASIEN_ID"=>$pasienid, "LOKASI_ID"=>"001", "AKTIF"=>"1"));
            return $sql;
        }
    }
?>
