# bin/sh




# Install frr
apt install -y curl gnupg2 traceroute
curl -s https://deb.frrouting.org/frr/keys.asc | sudo apt-key add -
FRRVER="frr-stable"
echo deb https://deb.frrouting.org/frr $(lsb_release -s -c) $FRRVER | sudo tee -a /etc/apt/sources.list.d/frr.list
sudo apt update -y && sudo apt install -y frr frr-pythontools

# 打开Frr全部功能
sed -i "s/=no/=yes/g" /etc/frr/daemons
service frr restart


# Wireguard
mkdir /tunnelbroker-wireguard
apt install -y wireguard
