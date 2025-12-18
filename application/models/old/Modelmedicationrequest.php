<?php
    class Modelmedicationrequest extends CI_Model{

        function medicationsingledose($env){
            $query =
                    "
                        SELECT Y.*,
                            (SELECT NAME FROM SR01_SATUSEHAT_FORM_MS  WHERE LOKASI_ID='001' AND CODE=Y.FORMCODE)FORMNAME,
                            (SELECT NAME FROM SR01_SATUSEHAT_ROUTE_MS WHERE LOKASI_ID='001' AND CODE=Y.ROUTECODE)ROUTENAME
                        FROM(
                            SELECT X.*,
                                (SELECT NAME           FROM SR01_SATUSEHAT_POA_MS WHERE LOKASI_ID='001' AND KFA_ID=X.KFAID)POANAME,
                                (SELECT FORM_CODE      FROM SR01_SATUSEHAT_POA_MS WHERE LOKASI_ID='001' AND KFA_ID=X.KFAID)FORMCODE,
                                (SELECT RUTE_PEMBERIAN FROM SR01_SATUSEHAT_POA_MS WHERE LOKASI_ID='001' AND KFA_ID=X.KFAID)ROUTECODE,
                                ROW_NUMBER() OVER (PARTITION BY X.TRANS_CO ORDER BY X.OBAT_ID ASC)NOURUT
                            FROM(
                                SELECT A.TRANS_CO, TRANS_ID, PASIEN_ID, EPISODE_ID, OBAT_ID, QTY, CREATED_BY, SIGNA_DOKTER, CATATAN,
                                    TO_CHAR(CREATED_DATE - INTERVAL '7' HOUR,'YYYY-MM-DD')||'T'||TO_CHAR(CREATED_DATE - INTERVAL '7' HOUR,'HH24:MI:SS')||'+00:00' TGLORDER,
                                    DENSE_RANK() OVER (ORDER BY A.EPISODE_ID ASC) AS NOURUT_EPISODE,

                                    (SELECT KFA_ID FROM SR01_FRM_OBAT_MS WHERE LOKASI_ID='001' AND AKTIF='1' AND OBAT_ID=A.OBAT_ID)KFAID,
                                    (SELECT RESOURCE_ID FROM SR01_SATUSEHAT_BUNDLE WHERE LOKASI_ID='001' AND AKTIF='1' AND POLI_ID IS NOT NULL AND RESOURCE_TYPE='Encounter' AND ENVIRONMENT='".$env."' AND PASIEN_ID=A.PASIEN_ID AND EPISODE_ID=A.EPISODE_ID AND CREATED_DATE=(SELECT MIN(CREATED_DATE) FROM SR01_SATUSEHAT_BUNDLE WHERE LOKASI_ID='001' AND AKTIF='1' AND POLI_ID IS NOT NULL AND RESOURCE_TYPE='Encounter' AND ENVIRONMENT='".$env."' AND PASIEN_ID=A.PASIEN_ID AND EPISODE_ID=A.EPISODE_ID ))ENCOUNTERID,

                                    (SELECT IHS_ID      FROM SR01_GEN_USER_DATA WHERE LOKASI_ID='001' AND AKTIF='1' AND UPPER(DOKTER_ID)=UPPER(A.CREATED_BY))PRACTITIONERID,
                                    (SELECT UPPER(NAMA) FROM SR01_GEN_USER_DATA WHERE LOKASI_ID='001' AND AKTIF='1' AND UPPER(DOKTER_ID)=UPPER(A.CREATED_BY))PRACTITIONERNAME,
                                                    
                                    SR01_GET_SUFFIX(A.PASIEN_ID)PATIENTNAME,
                                    (SELECT INT_PASIEN_ID FROM SR01_GEN_PASIEN_MS WHERE LOKASI_ID='001' AND AKTIF='1' AND PASIEN_ID=A.PASIEN_ID)PATIENTMR,
                                    (SELECT SATUSEHAT_ID  FROM SR01_GEN_PASIEN_MS WHERE LOKASI_ID='001' AND AKTIF='1' AND PASIEN_ID=A.PASIEN_ID)PATIENTID
                                            
                                FROM WEB_CO_RESEP_DT A
                                WHERE A.SHOW_ITEM='1'
                                AND   A.TYPE='00'
                                AND   A.OBAT_ID NOT IN ('VAKSI0000000012','VAKSI0000000013')
                                AND   A.OBAT_ID NOT IN (SELECT OBAT_ID FROM SR01_SATUSEHAT_MEDICATION WHERE LOKASI_ID='001' AND AKTIF='1' AND PASIEN_ID=A.PASIEN_ID AND EPISODE_ID=A.EPISODE_ID AND OBAT_ID=A.OBAT_ID AND NOTE='REQUEST')
                                AND   A.EPISODE_ID  IN (SELECT EPISODE_ID FROM SR01_SATUSEHAT_BUNDLE WHERE LOKASI_ID='001' AND AKTIF='1' AND POLI_ID IS NOT NULL AND RESOURCE_TYPE='Encounter' AND ENVIRONMENT='".$env."' AND PASIEN_ID=A.PASIEN_ID AND EPISODE_ID=A.EPISODE_ID)
                                AND   A.PASIEN_ID    =(SELECT PASIEN_ID FROM SR01_GEN_PASIEN_MS WHERE LOKASI_ID='001' AND AKTIF='1' AND ACC_SATUSEHAT='Y' AND SATUSEHAT_ID IS NOT NULL AND PASIEN_ID=A.PASIEN_ID)
                                AND   A.OBAT_ID      =(SELECT OBAT_ID FROM SR01_FRM_OBAT_MS WHERE OBAT_ID=A.OBAT_ID AND KFA_ID IS NOT NULL)
                                AND   A.CREATED_BY   =(SELECT DOKTER_ID FROM SR01_GEN_USER_DATA WHERE LOKASI_ID='001' AND AKTIF='1' AND IHS_ID IS NOT NULL AND IHS_ID<>'NOT FOUND' AND DOKTER_ID=A.CREATED_BY)
                            )X
                            WHERE X.NOURUT_EPISODE <= 2
                        )Y
                    ";

			$recordset = $this->db->query($query);
			$recordset = $recordset->result();
			return $recordset;
        }

    }
?>


