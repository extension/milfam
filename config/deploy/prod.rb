set :deploy_to, "/services/milfam/"
set :branch, 'master'
set :vhost, 'militaryfamilies.extension.org'
set :deploy_server, 'militaryfamilies.awsi.extension.org'
server deploy_server, :app, :web, :db, :primary => true
