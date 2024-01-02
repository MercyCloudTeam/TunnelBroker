# bin/sh

apt install -y git gcc autoconf libtool || yum install -y git gcc autoconf libtool

git clone https://github.com/bgp/bgpq4.git && cd bgpq4

./bootstrap
./configure
make
make install
