module Capistrano
  class FileNotFound < StandardError
  end
end

namespace :deploy do
  task :updated do
    invoke "lorry:cache:clear"
    invoke "doctrine:orm:generate_proxies"
    invoke "lorry:cache:warmup"
  end
  task :published do
    invoke "lorry:resque:restart"
  end
end
