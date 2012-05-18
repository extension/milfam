set :deploy_to, '/services/apache/vhosts/militaryfamilies.demo.extension.org/docroot/'
server 'militaryfamilies.demo.extension.org', :app, :web, :db, :primary => true
