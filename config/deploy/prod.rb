set :deploy_to, "/services/milfam/"
set :vhost, 'militaryfamilies.extension.org'
server 'leaisland.vm.extension.org', :app, :web, :db, :primary => true
