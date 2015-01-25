module Capistrano
  class FileNotFound < StandardError
  end
end

namespace :deploy do
  task :updated do
    invoke "lorry:cache:warmup"
  end
end
