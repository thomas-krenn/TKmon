# -*- mode: ruby -*-
# vi: set ft=ruby :
Vagrant.configure(2) do |config|
  config.vm.box = "parallels/ubuntu-14.04"
  config.vm.network "forwarded_port", guest: 80, host: 8085
  config.vm.provision "shell", run: "always", inline: <<-SHELL
    export DEBIAN_FRONTEND=noninteractive
    if [[ -z $(ls -A /var/lib/apt/lists/) ]] ||
       [[ $(find /var/lib/apt/lists/ -ctime +1 | wc -l) -gt 2 ]]
    then
        sudo apt-get update
    else
        echo "Apt list update within 24 hours, abort."
    fi
    sudo apt-get install -y \
        language-pack-en-base \
        language-pack-en \
        locales \
        ansible
    locale-gen en_US.UTF-8 && \
        /usr/sbin/update-locale LANG=en_US.UTF-8
    export LC_ALL=en_US.UTF-8
  SHELL
  config.vm.provision "ansible" do |ansible|
    # ansible.verbose = "vvv"
    ansible.playbook = ".vagrant-provision/ansible/playbook.yml"
  end
end
