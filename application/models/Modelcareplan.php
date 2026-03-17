<?php
    class Modelcareplan extends CI_Model{

        function careplan($env){
            $query =
                    "
                        SELECT 
                            E.PASIEN_ID,
                            E.EPISODE_ID,
                            D.TRANS_SOAP,
                            D.P,

                            TO_CHAR(D.CREATED_DATE - INTERVAL '7' HOUR,'YYYY-MM-DD') ||
                            'T' ||
                            TO_CHAR(D.CREATED_DATE - INTERVAL '7' HOUR,'HH24:MI:SS') ||
                            '+00:00' CREATED_DATE,

                            E.RESOURCE_ID AS RESOURCEID,
                            E.POLI_ID,
                            E.DOKTER_ID,

                            -- Patient
                            SR01_GET_SUFFIX(E.PASIEN_ID) AS PATIENTNAME,
                            P.INT_PASIEN_ID AS PATIENTMR,
                            P.SATUSEHAT_ID  AS PATIENTID,

                            -- Practitioner
                            U.IHS_ID        AS PRACTITIONERID,
                            UPPER(U.NAMA)   AS PRACTITIONERNAME

                        FROM SR01_SATUSEHAT_TRANSAKSI E

                        JOIN WEB_CO_DIAGNOSA_DT D
                            ON D.PASIEN_ID = E.PASIEN_ID
                            AND D.EPISODE_ID = E.EPISODE_ID
                            AND D.LOKASI_ID = '001'
                            AND D.SHOW_ITEM = '1'

                        LEFT JOIN SR01_GEN_PASIEN_MS P
                            ON P.PASIEN_ID = E.PASIEN_ID
                            AND P.LOKASI_ID = '001'
                            AND P.AKTIF = '1'

                        LEFT JOIN SR01_GEN_USER_DATA U
                            ON U.DOKTER_ID = D.CREATED_BY
                            AND U.LOKASI_ID = '001'

                        WHERE E.LOKASI_ID='001'
                        AND   E.AKTIF='1'
                        AND   E.RESOURCE_TYPE='Encounter'
                        AND   E.JENIS='1'
                        AND   E.ENVIRONMENT='".$env."'

                        AND NOT EXISTS (
                            SELECT 1
                            FROM SR01_SATUSEHAT_TRANSAKSI X
                            WHERE X.LOKASI_ID='001'
                            AND X.AKTIF='1'
                            AND X.RESOURCE_TYPE='CarePlan'
                            AND X.JENIS='1'
                            AND X.ENVIRONMENT='".$env."'
                            AND X.PASIEN_ID=E.PASIEN_ID
                            AND X.EPISODE_ID=E.EPISODE_ID
                        )

                        FETCH FIRST 10 ROWS ONLY
                    ";

			$recordset = $this->db->query($query);
			$recordset = $recordset->result();
			return $recordset;
        }
    }
?>
