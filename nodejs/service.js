import os from "os";
import fetch from "node-fetch";
import chalk from "chalk";

let lebar = 180;
let host  = "localhost";

const interfaces = os.networkInterfaces();
for (const iface of Object.values(interfaces)) {
	for (const info of iface) {
		if (info.family === "IPv4" && !info.internal) {
			host = info.address;
			break;
		}
	}
}

const BASE_URL = process.env.BASE_URL || `http://${host}/rsudpasarminggu/prod/satusehat/index.php/`;

function getTimeStamp() {
    const now = new Date();
    const pad = (n) => n.toString().padStart(2, "0");
    return `${now.getFullYear()}-${pad(now.getMonth() + 1)}-${pad(now.getDate())} ${pad(now.getHours())}:${pad(now.getMinutes())}:${pad(now.getSeconds())}`;
}

function logHeader() {
	console.log(chalk.cyan("=".repeat(lebar)));
	console.log(
		chalk.cyan(
			"TIMESTAMP".padEnd(24) +
			"METHOD".padEnd(9) +
			"ENDPOINT".padEnd(32) +
			"STATUS".padEnd(16) +
			"MESSAGE"
		)
	);
	console.log(chalk.cyan("=".repeat(lebar)));
}

function logRow(timestamp, method, endpoint, status, message, statusColor = "green") {
	const colorStatus =
		statusColor === "red"    ? chalk.red :
		statusColor === "yellow" ? chalk.yellow :
		chalk.green;

	console.log(
		chalk.white(timestamp.padEnd(24)) +
		chalk.white(method.padEnd(9)) +
		chalk.white(endpoint.padEnd(32)) +
		colorStatus(String(status).padEnd(16)) +
		chalk.white(message)
	);
}


async function callAPI(endpoint, method = "GET", body = null) {
	const url = `${BASE_URL}${endpoint}`;
	const options = {
		method,
		headers: { "Content-Type": "application/json" }
	};

	if (body) options.body = JSON.stringify(body);

	try {
		const response = await fetch(url, options);
		const text     = await response.text();
		const timestamp = getTimeStamp();

		logHeader();

		if (!response.ok) {
			logRow(
				timestamp,
				method,
				endpoint,
				response.status,
				response.statusText,
				"red"
			);
			return;
		}

		// JSON response
		try {
			const data = JSON.parse(text);
			logRow(
				timestamp,
				method,
				endpoint,
				response.status,
				"OK"
			);
			console.log(chalk.gray(JSON.stringify(data, null, 2)));
		} catch {
			// Plain text response
			logRow(
				timestamp,
				method,
				endpoint,
				response.status,
				response.statusText
			);
			console.log(chalk.gray(text));
		}

	} catch (error) {
		logHeader();
		logRow(getTimeStamp(),method,endpoint,"NETWORK",error.message,"red");
	}
}

async function runservices(){
	await callAPI("orderrad", "POST");
}

// async function runservicesbundle(){
// 	await callAPI("poliklinik", "POST");
// }

console.clear();
runservices();
// runservicesbundle();
setInterval(runservices, 5000);
// setInterval(runservicesbundle, 10000);