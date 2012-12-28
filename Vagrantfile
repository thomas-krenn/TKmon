# -*- mode: ruby -*-
# vi: set ft=ruby :

Vagrant::Config.run do |config|
  config.vm.box = "tkmon"
  config.vm.box_url = "https://boxes.itsocks.de/vagrant/tkmon.box"
  config.vm.network :hostonly, "10.121.0.35"
  config.vm.forward_port 80, 8080
end
