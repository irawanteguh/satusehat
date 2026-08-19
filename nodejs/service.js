import fetch from "node-fetch";
import chalk from "chalk";

const lebar = 180;

const BASE_URL =
    process.env.BASE_URL ||
    "http://192.168.200.41:8080/satusehat/index.php/";

const REQUEST_TIMEOUT = 5 * 60 * 1000; // 5 menit


/*
|--------------------------------------------------------------------------
| SERVICE QUEUE
|--------------------------------------------------------------------------
*/

const services = [

    "patientid",

    "poliklinik",
    "anamnesaawalrj",
    "hasillab",
    "orderrad",
    "orderlab",
    "specimenlab",
    "dicom",
    "diaglaboratorium",
    "careplan",

    // "allergyintolerance",

    "radiologi",
    "cipoliklinik",

    // "qpoliklinik",

    "compoliklinik",
    "singledose",
    "singledosereq"

];


console.log(
    chalk.cyan(`BASE URL : ${BASE_URL}`)
);

console.log(
    chalk.cyan(
        `REQUEST TIMEOUT : ${REQUEST_TIMEOUT / 60000} MENIT`
    )
);


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
    method = "GET",
    body = null
) {

    const url = `${BASE_URL}${endpoint}`;

    const controller = new AbortController();

    /*
    |--------------------------------------------------------------------------
    | TIMEOUT 5 MENIT
    |--------------------------------------------------------------------------
    */

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


    const startTime = Date.now();


    try {

        console.log(
            chalk.yellow(
                `\n>>> START : ${endpoint}`
            )
        );

        console.log(
            chalk.gray(
                `URL     : ${url}`
            )
        );

        console.log(
            chalk.gray(
                `TIMEOUT : ${REQUEST_TIMEOUT / 60000} menit`
            )
        );


        const response = await fetch(
            url,
            options
        );


        const text = await response.text();


        const duration =
            ((Date.now() - startTime) / 1000).toFixed(2);


        clearTimeout(timeout);


        logHeader();


        /*
        |--------------------------------------------------------------------------
        | HTTP ERROR
        |--------------------------------------------------------------------------
        */

        if (!response.ok) {

            logRow(
                getTimeStamp(),
                method,
                endpoint,
                response.status,
                `${response.statusText} | ${duration}s`,
                "red"
            );

            console.log(
                chalk.red(
                    `<<< ERROR : ${endpoint}`
                )
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
                getTimeStamp(),
                method,
                endpoint,
                response.status,
                `${response.statusText} | ${duration}s`,
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


            console.log(
                chalk.green(
                    `<<< FINISH : ${endpoint} | ${duration}s`
                )
            );


            return data;


        } catch {

            /*
            |--------------------------------------------------------------------------
            | PLAIN TEXT
            |--------------------------------------------------------------------------
            */

            logRow(
                getTimeStamp(),
                method,
                endpoint,
                response.status,
                `${response.statusText} | ${duration}s`,
                "green"
            );


            console.log(
                chalk.gray(text)
            );


            console.log(
                chalk.green(
                    `<<< FINISH : ${endpoint} | ${duration}s`
                )
            );


            return text;

        }


    } catch (error) {

        clearTimeout(timeout);


        const duration =
            ((Date.now() - startTime) / 1000).toFixed(2);


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
                `Request lebih dari 5 menit | ${duration}s`,
                "red"
            );


            console.log(
                chalk.red(
                    `<<< TIMEOUT : ${endpoint}`
                )
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
            `${error.message} | ${duration}s`,
            "red"
        );


        console.log(
            chalk.red(
                `<<< NETWORK ERROR : ${endpoint}`
            )
        );


        return null;

    }

}


/*
|--------------------------------------------------------------------------
| RUN ALL SERVICES
|--------------------------------------------------------------------------
*/

async function runServices() {

    console.log(
        chalk.cyan(
            "\n\n=============================================="
        )
    );

    console.log(
        chalk.cyan(
            "START SERVICE QUEUE"
        )
    );

    console.log(
        chalk.cyan(
            `TOTAL SERVICE : ${services.length}`
        )
    );

    console.log(
        chalk.cyan(
            "==============================================\n"
        )
    );


    for (let i = 0; i < services.length; i++) {

        const endpoint = services[i];


        console.log(
            chalk.blue(
                `\n[${i + 1}/${services.length}] ${endpoint}`
            )
        );


        /*
        |--------------------------------------------------------------------------
        | CALL SERVICE
        |--------------------------------------------------------------------------
        */

        await callAPI(
            endpoint,
            "POST"
        );


        /*
        |--------------------------------------------------------------------------
        | SERVICE SELESAI / ERROR / TIMEOUT
        |--------------------------------------------------------------------------
        |
        | Apapun hasilnya, langsung lanjut service berikutnya.
        |
        */

        console.log(
            chalk.gray(
                `NEXT SERVICE : ${
                    services[i + 1] || services[0]
                }`
            )
        );

    }


    /*
    |--------------------------------------------------------------------------
    | SEMUA SELESAI
    |--------------------------------------------------------------------------
    */

    console.log(
        chalk.cyan(
            "\n=============================================="
        )
    );

    console.log(
        chalk.cyan(
            "SEMUA SERVICE SELESAI"
        )
    );

    console.log(
        chalk.cyan(
            "RESTART DARI SERVICE PERTAMA"
        )
    );

    console.log(
        chalk.cyan(
            "==============================================\n"
        )
    );

}


/*
|--------------------------------------------------------------------------
| MAIN LOOP
|--------------------------------------------------------------------------
*/

async function main() {

    console.clear();


    console.log(
        chalk.cyan(
            "SATUSEHAT SERVICE STARTED"
        )
    );


    console.log(
        chalk.cyan(
            `BASE URL : ${BASE_URL}`
        )
    );


    console.log(
        chalk.cyan(
            `TOTAL SERVICE : ${services.length}`
        )
    );


    /*
    |--------------------------------------------------------------------------
    | LOOP SELAMANYA
    |--------------------------------------------------------------------------
    */

    while (true) {

        try {

            await runServices();

        } catch (error) {

            console.log(
                chalk.red(
                    `MAIN LOOP ERROR : ${error.message}`
                )
            );

        }

    }

}


main();