set :deploy_to, '/services/apache/vhosts/militaryfamilies.extension.org/docroot/'
server 'militaryfamilies.extension.org', :app, :web, :db, :primary => true

