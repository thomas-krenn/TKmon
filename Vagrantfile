# -*- mode: ruby -*-
# vi: set ft=ruby :
Vagrant.configure(2) do |config|
  config.vm.box = "ubuntu/trusty64"

  config.vm.network "forwarded_port", guest: 80, host: 8085
  config.vm.network "forwarded_port", guest: 8086, host: 8086
  config.vm.hostname = "tkmon"

  config.vm.provider :virtualbox do |vb, override|
    vb.name = "tkmon-development"
    vb.vbguest.auto_update = true
    vb.customize ["modifyvm", :id, "--memory", "1024"]
    vb.customize ["modifyvm", :id, "--cpus", "2"]
  end

  config.vm.provider :parallels do |p, override|
    override.vm.box = "parallels/ubuntu-14.04"
    p.name = "tkmon-development"
    p.update_guest_tools = true
    p.memory = 1024
    p.cpus = 2
    p.customize ["set", :id, "--longer-battery-life", "off"]
  end

  config.vm.provision "shell", run: "always", inline: <<-SHELL
    export DEBIAN_FRONTEND=noninteractive
    if [[ -z $(ls -A /var/lib/apt/lists/) ]] ||
       [[ $(find /var/lib/apt/lists/ -ctime +1 | wc -l) -gt 2 ]]
    then
        apt-get update && apt-get upgrade -y
    else
        echo "Apt list update within 24 hours, abort."
    fi
    sudo apt-get install -y --no-install-recommends \
        language-pack-en-base \
        language-pack-en \
        locales
    locale-gen en_US.UTF-8 && \
        /usr/sbin/update-locale LANG=en_US.UTF-8
    export LC_ALL=en_US.UTF-8
    export LANG=en_US.UTF-8
    export TZ=Europe/Berlin
    echo $TZ > /etc/timezone \
      && dpkg-reconfigure --frontend noninteractive tzdata
  SHELL

  config.vm.provision "ansible" do |ansible|
    # ansible.verbose = "vvv"
    ansible.playbook = ".vagrant-provision/ansible/playbook.yml"
  end
end
