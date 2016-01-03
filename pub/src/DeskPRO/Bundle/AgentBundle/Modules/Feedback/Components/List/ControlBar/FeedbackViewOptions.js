const defaultCardFields = [
  'id',
  'title',
  'person',
  'status',
  'labels',
  'category',
  'date_created'
];

const defaultTableFields = [
  'id',
  'num_ratings',
  'title',
  'content',
  'hidden_status',
  'status_category',
  'type',
  'category',
  'labels',
  'author_name',
  'num_comments',
  'date_created',
  'total_rating'
];

const defaultCommentTableFields = ['comment_id', 'comment_content', 'comment_author'];

module.exports.defaultCardFields = defaultCardFields;
module.exports.defaultTableFields = defaultTableFields;
module.exports.defaultCommentTableFields = defaultCommentTableFields;
