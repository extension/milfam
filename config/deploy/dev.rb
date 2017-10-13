set :deploy_to, "/services/milfam/"
if(branch = ENV['BRANCH'])
  set :branch, branch
else
  set :branch, 'master'
end
set :vhost, 'dev-militaryfamilies.extension.org'
set :deploy_server, 'dev-militaryfamilies.awsi.extension.org'
server deploy_server, :app, :web, :db, :primary => true
