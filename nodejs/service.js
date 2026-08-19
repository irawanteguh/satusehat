import os from "os";
import fetch from "node-fetch";
import chalk from "chalk";

const lebar = 180;

let isRunningServices       = false;
let isRunningServicesBundle = false;

/*
|--------------------------------------------------------------------------
| BASE URL
|--------------------------------------------------------------------------
| Prioritas:
| 1. BASE_URL dari environment
| 2. URL server yang sudah ditentukan
|
| Jangan mengambil IP otomatis dari os.networkInterfaces()
| karena jika Node berjalan di Docker, IP yang didapat bisa
| menjadi IP network/container seperti 172.22.x.x
|--------------------------------------------------------------------------
*/

const BASE_URL =
    process.env.BASE_URL ||
    "http://192.168.200.41:8080/satusehat/index.php/";

console.log(
    chalk.cyan(`BASE URL : ${BASE_URL}`)
);


function getTimeStamp() {

    const now = new Date();

    const pad = (n) =>
        n.toString().padStart(2, "0");

    return (
        `${now.getFullYear()}-` +
        `${pad(now.getMonth() + 1)}-` +
        `${pad(now.getDate())} ` +
        `${pad(now.getHours())}:` +
        `${pad(now.getMinutes())}:` +
        `${pad(now.getSeconds())}`
    );
}


function logHeader() {

    console.log(
        chalk.cyan("=".repeat(lebar))
    );

    console.log(
        chalk.cyan(
            "TIMESTAMP".padEnd(24) +
            "METHOD".padEnd(9) +
            "ENDPOINT".padEnd(32) +
            "STATUS".padEnd(16) +
            "MESSAGE"
        )
    );

    console.log(
        chalk.cyan("=".repeat(lebar))
    );
}


function logRow(
    timestamp,
    method,
    endpoint,
    status,
    message,
    statusColor = "green"
) {

    const colorStatus =
        statusColor === "red"
            ? chalk.red
            : statusColor === "yellow"
                ? chalk.yellow
                : chalk.green;

    console.log(
        chalk.white(timestamp.padEnd(24)) +
        chalk.white(method.padEnd(9)) +
        chalk.white(endpoint.padEnd(32)) +
        colorStatus(String(status).padEnd(16)) +
        chalk.white(message)
    );
}


async function callAPI(
    endpoint,
    method = "GET",
    body = null
) {

    const url = `${BASE_URL}${endpoint}`;

    const options = {
        method,
        headers: {
            "Content-Type": "application/json"
        }
    };

    if (body !== null) {
        options.body = JSON.stringify(body);
    }

    try {

        const response = await fetch(
            url,
            options
        );

        const text = await response.text();

        const timestamp = getTimeStamp();

        logHeader();

        /*
        |--------------------------------------------------------------------------
        | HTTP ERROR
        |--------------------------------------------------------------------------
        */

        if (!response.ok) {

            logRow(
                timestamp,
                method,
                endpoint,
                response.status,
                `${response.statusText} : ${url}`,
                "red"
            );

            return null;
        }


        /*
        |--------------------------------------------------------------------------
        | JSON RESPONSE
        |--------------------------------------------------------------------------
        */

        try {

            const data = JSON.parse(text);

            logRow(
                timestamp,
                method,
                endpoint,
                response.status,
                `${response.statusText} : ${url}`,
                "green"
            );

            console.log(
                chalk.gray(
                    JSON.stringify(
                        data,
                        null,
                        2
                    )
                )
            );

            return data;

        } catch {

            /*
            |--------------------------------------------------------------------------
            | PLAIN TEXT RESPONSE
            |--------------------------------------------------------------------------
            */

            logRow(
                timestamp,
                method,
                endpoint,
                response.status,
                response.statusText,
                "green"
            );

            console.log(
                chalk.gray(text)
            );

            return text;
        }

    } catch (error) {

        logHeader();

        logRow(
            getTimeStamp(),
            method,
            endpoint,
            "NETWORK",
            `${error.message} : ${url}`,
            "red"
        );

        return null;
    }
}


function Waiting(endpoint) {

    logHeader();

    logRow(
        getTimeStamp(),
        "WAIT",
        endpoint,
        "WAITING",
        "Proses sebelumnya masih berjalan",
        "yellow"
    );
}


async function runservices() {

    if (isRunningServices) {

        Waiting("patientid");

        return;
    }

    isRunningServices = true;

    try {

        await callAPI(
            "patientid",
            "POST"
        );

    } finally {

        isRunningServices = false;
    }
}


async function runservicesbundle() {

    if (isRunningServicesBundle) {

        Waiting("bundle-services");

        return;
    }

    isRunningServicesBundle = true;

    try {

        await callAPI("poliklinik", "POST");
        await callAPI("anamnesaawalrj", "POST");
        await callAPI("hasillab", "POST");
        await callAPI("orderrad", "POST");
        await callAPI("orderlab", "POST");
        await callAPI("specimenlab", "POST");
        await callAPI("dicom", "POST");
        await callAPI("diaglaboratorium", "POST");
        await callAPI("careplan", "POST");
        await callAPI("allergyintolerance", "POST");
        await callAPI("radiologi", "POST");
        await callAPI("cipoliklinik", "POST");
        await callAPI("qpoliklinik", "POST");
        await callAPI("compoliklinik", "POST");
        await callAPI("singledose", "POST");
        await callAPI("singledosereq", "POST");

    } finally {

        isRunningServicesBundle = false;
    }
}


console.clear();

console.log(
    chalk.cyan("SATUSEHAT SERVICE STARTED")
);

console.log(
    chalk.cyan(`BASE URL : ${BASE_URL}`)
);


/*
|--------------------------------------------------------------------------
| INITIAL RUN
|--------------------------------------------------------------------------
*/

runservices();
runservicesbundle();


/*
|--------------------------------------------------------------------------
| SCHEDULER
|--------------------------------------------------------------------------
*/

setInterval(
    runservices,
    5000
);

setInterval(
    runservicesbundle,
    10000
);