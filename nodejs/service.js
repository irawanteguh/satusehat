import fetch from "node-fetch";
import chalk from "chalk";

const lebar = 130;

const BASE_URL =
    process.env.BASE_URL ||
    "http://192.168.200.41:8080/satusehat/index.php/";

console.clear();

console.log(
    chalk.cyan("SATUSEHAT SERVICE STARTED")
);

console.log(
    chalk.cyan(`BASE URL : ${BASE_URL}`)
);


/*
|--------------------------------------------------------------------------
| SERVICE QUEUE
|--------------------------------------------------------------------------
*/

const services = [
    "patientid",
    // "poliklinik",
    // "anamnesaawalrj",
    // "hasillab",
    // "orderrad",
    // "orderlab",
    // "specimenlab",
    // "dicom",
    // "diaglaboratorium",
    // "careplan",
    // "radiologi",
    // "cipoliklinik",
    // "compoliklinik",
    // "singledose",
    // "singledosereq"
];


/*
|--------------------------------------------------------------------------
| TIMEOUT
|--------------------------------------------------------------------------
|
| Maksimal 5 menit per request
|
*/

const REQUEST_TIMEOUT = 5 * 60 * 1000;


/*
|--------------------------------------------------------------------------
| TIMESTAMP
|--------------------------------------------------------------------------
*/

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


/*
|--------------------------------------------------------------------------
| LOG HEADER
|--------------------------------------------------------------------------
*/

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


/*
|--------------------------------------------------------------------------
| LOG ROW
|--------------------------------------------------------------------------
*/

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


/*
|--------------------------------------------------------------------------
| CALL API
|--------------------------------------------------------------------------
*/

async function callAPI(
    endpoint,
    method = "POST",
    body = null
) {

    const url = `${BASE_URL}${endpoint}`;

    const controller = new AbortController();

    const timeout = setTimeout(() => {
        controller.abort();
    }, REQUEST_TIMEOUT);


    const options = {
        method,

        headers: {
            "Content-Type": "application/json"
        },

        signal: controller.signal
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


        /*
        |--------------------------------------------------------------------------
        | TIMEOUT
        |--------------------------------------------------------------------------
        */

        if (error.name === "AbortError") {

            logRow(
                getTimeStamp(),
                method,
                endpoint,
                "TIMEOUT",
                `Request timeout : ${url}`,
                "yellow"
            );

            return null;
        }


        /*
        |--------------------------------------------------------------------------
        | NETWORK ERROR
        |--------------------------------------------------------------------------
        */

        logRow(
            getTimeStamp(),
            method,
            endpoint,
            "NETWORK",
            `${error.message} : ${url}`,
            "red"
        );

        return null;


    } finally {

        clearTimeout(timeout);

    }
}


/*
|--------------------------------------------------------------------------
| RUN ALL SERVICES
|--------------------------------------------------------------------------
*/

async function runServices() {

    while (true) {

        for (const endpoint of services) {

            try {

                await callAPI(
                    endpoint,
                    "POST"
                );

            } catch (error) {

                /*
                |--------------------------------------------------------------------------
                | Jangan sampai satu service menghentikan queue
                |--------------------------------------------------------------------------
                */

                logHeader();

                logRow(
                    getTimeStamp(),
                    "POST",
                    endpoint,
                    "ERROR",
                    error.message,
                    "red"
                );

            }

        }

    }

}


/*
|--------------------------------------------------------------------------
| START
|--------------------------------------------------------------------------
*/

runServices();