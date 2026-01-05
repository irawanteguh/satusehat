<?php
    class Modelallergyintolerance extends CI_Model{

        function alergi($env){
            $query =
                    "
                        SELECT X.*
                        FROM(
                        SELECT /*+ USE_NL(A PAS U EP ST) */
                            DISTINCT
                            A.PASIEN_ID, A.CREATED_BY,

                            TO_CHAR(A.CREATED_DATE - INTERVAL '7' HOUR,'YYYY-MM-DD') || 'T' ||
                            TO_CHAR(A.CREATED_DATE - INTERVAL '7' HOUR,'HH24:MI:SS') || '+00:00' AS CREATED_DATE,

                            -- Patient
                            SR01_GET_SUFFIX(A.PASIEN_ID) AS PATIENTNAME,
                            PAS.INT_PASIEN_ID            AS PATIENTMR,
                            PAS.SATUSEHAT_ID             AS PATIENTID,

                            -- Practitioner
                            U.IHS_ID                     AS PRACTITIONERID,
                            UPPER(U.NAMA)                AS PRACTITIONERNAME,

                            -- Episode
                            EP.EPISODE_ID,

                            -- Encounter (Satusehat)
                            ST.RESOURCE_ID               AS RESOURCEID,
                            ST.POLI_ID,
                            ST.DOKTER_ID

                        FROM WEB_CO_ALERGI_DT A

                        JOIN SR01_GEN_PASIEN_MS PAS
                            ON PAS.PASIEN_ID = A.PASIEN_ID
                            AND PAS.LOKASI_ID = '001'
                            AND PAS.AKTIF     = '1'

                        LEFT JOIN SR01_GEN_USER_DATA U
                            ON (
                                    U.DOKTER_ID = A.CREATED_BY
                                OR 'SIRS01_'||U.USER_ID =  A.CREATED_BY
                                )
                            AND U.LOKASI_ID = '001'
                            AND U.AKTIF     = '1'

                        LEFT JOIN SR01_KEU_EPISODE EP
                            ON EP.PASIEN_ID = A.PASIEN_ID
                            AND EP.AKTIF     = '1'
                            AND A.CREATED_DATE >= EP.TGL_MASUK
                            AND A.CREATED_DATE <  NVL(EP.TGL_KELUAR + 1, A.CREATED_DATE + 1)

                        LEFT JOIN SR01_SATUSEHAT_TRANSAKSI ST
                            ON ST.LOKASI_ID     = '001'
                            AND ST.AKTIF         = '1'
                            AND ST.RESOURCE_TYPE = 'Encounter'
                            AND ST.JENIS         = '1'
                            AND ST.ENVIRONMENT   = '".$env."'
                            AND ST.PASIEN_ID     = A.PASIEN_ID
                            AND ST.EPISODE_ID    = EP.EPISODE_ID

                        WHERE A.SHOW_ITEM = '1'
                        AND   UPPER(A.ALERGI) = 'TIDAK ADA'
                        AND   A.CREATED_BY<>'SIMRS_MANAGER'
                        AND   EXISTS (
                                SELECT 1
                                FROM SR01_SATUSEHAT_TRANSAKSI T
                                WHERE T.LOKASI_ID     = '001'
                                AND   T.AKTIF         = '1'
                                AND   T.RESOURCE_TYPE = 'Encounter'
                                AND   T.JENIS         = '1'
                                AND   T.ENVIRONMENT   = '".$env."'
                                AND   T.PASIEN_ID     = A.PASIEN_ID
                                AND   T.EPISODE_ID    = EP.EPISODE_ID
                            )
                        AND   NOT EXISTS (
                                            SELECT 1
                                            FROM SR01_SATUSEHAT_TRANSAKSI T
                                            WHERE T.LOKASI_ID     = '001'
                                            AND   T.AKTIF         = '1'
                                            AND   T.RESOURCE_TYPE = 'AllergyIntolerance'
                                            AND   T.JENIS         = '1'
                                            AND   T.ENVIRONMENT   = '".$env."'
                                            AND   T.PASIEN_ID     = A.PASIEN_ID
                                            AND   T.EPISODE_ID    = EP.EPISODE_ID
                                        )
                        )X
                        WHERE X.PRACTITIONERID IS NOT NULL
                        FETCH FIRST 1 ROWS ONLY

                    ";

			$recordset = $this->db->query($query);
			$recordset = $recordset->result();
			return $recordset;
        }
    }
?>
