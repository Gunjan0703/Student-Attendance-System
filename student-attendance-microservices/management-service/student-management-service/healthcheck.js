const http = require("http");

const options = {
  hostname: "127.0.0.1",   // Force IPv4
  port: 3001,
  path: "/health",
  method: "GET",
  timeout: 200
};

const req = http.request(options, (res) => {
  if (res.statusCode === 200) {
    process.exit(0);
  } else {
    process.exit(1);
  }
});

req.on("error", () => process.exit(1));
req.on("timeout", () => { 
  req.destroy();
  process.exit(1);
});

req.end();
