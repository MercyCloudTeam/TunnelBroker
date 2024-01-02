<p align="center"><a href="https://ngunion.com" target="_blank"><img src="http://ngunion.com/assets/img/logow.png" width="150"></a></p>

<p align="center">

</p>

## About this Project

This is a Tunnel Broker panel developed using the Laravel framework and its ecosystem.

> IPV6 Tunnel allows you to establish a tunnel through a host or router with only IPv4 connection and supporting IPv6, enabling access to IPv6 Internet.

### Supported Tunnels

* sit(6in4)
* wireguard
* gre
* ipip
* vxlan

### Operational Principle

> This project does not require any Agent software, and the nodes do not need to install or configure anything. Simply deploy the code from this repository to the web server to use (the server needs to install the basic software FRRouting).

The web server executes commands on the node server remotely through SSH.

### Node Dependency Packages

* iproute2
* frrouting
* wireguard

## Features

* User system (basic user system [registration, login, personal center], does not include functions like ticket system)
* Automatic configuration of Wireguard SIT GRE IPIP VXLAN ... Tunnel

(After the update on February 14, 2023, this feature is temporarily unavailable. If you need to use it, please switch to the old version)
* Automatic configuration of FRRouting router BGP
* Automatic update of AS-SET (based on RIPE NCC RESTful API)
* Automatic verification of ASN (only when the registered email is the email of the ASN maintainer)

## Case Study

TunnelBroker.io

TunnelBrokerIO is a public welfare project, supported by sponsors and maintained and technically supported by MercyCloud & NGUnion.

## Sponsors

We are currently looking for sponsors. If you are interested in supporting this project, please feel free to contact us.

> Maintaining this project requires more personnel and resources. If you are interested in providing sponsorship, please contact us by email or other methods.

## Notice

* This project is still in the early stages of development, and its features may not be stable yet.
* This project will be a transition project for "IPV6ization".
* For users who deploy by themselves:
    * This project did not initially make any design for charging and profiting. Deploying this project and operating it will basically not bring you any profit.
    * Managing this project requires a certain amount of network experience and a certain development ability to deal with program bugs.

## Thanks

Project

* Dcat Admin
* Laravel Framework
* Laravel Telescope
* phpseclib

## Contact

Support: support@isif.net (Normal support, service email)

NOC:  noc@isif.net (Network issues)

> This is an open source project, our response time may be longer, please do not frequently send reminder and spam emails. These emails are very important to our daily operations, Thanks.

Telegram: [MercyCloud Channel(Operational Channel)](https://t.me/MercyCloudTips) | [NGUnion Channel(Development Channel)](https://t.me/NGUnion) | [YFsama(Developer)](https://t.me/YFsama)

## License

MIT

