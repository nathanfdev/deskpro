jest.dontMock('DeskPRO/Bundle/AgentBundle/Services/Api/Content/Comments.js');
jest.dontMock('DeskPRO/Bundle/AgentBundle/Services/Api/Content/Content.js');
jest.dontMock('DeskPRO/Bundle/AgentBundle/Services/DpApi.js');

describe('API Comments service', () => {

  const DpApi = require('DeskPRO/Bundle/AgentBundle/Services/DpApi.js');
  const Comments = require('DeskPRO/Bundle/AgentBundle/Services/Api/Content/Comments.js');

  describe('load()', () => {
    it('should load article comments', () => {
      spyOn(DpApi, 'sendGet');
      Comments.load('articles');
      expect(DpApi.sendGet.argsForCall[0][0]).toMatch(/DP_API\/article_comments*/);
    });

    it('should load news comments', () => {
      spyOn(DpApi, 'sendGet');
      Comments.load('news');
      expect(DpApi.sendGet.argsForCall[0][0]).toMatch(/DP_API\/news_comments*/);
    });

    it('should load downloads comments', () => {
      spyOn(DpApi, 'sendGet');
      Comments.load('downloads');
      expect(DpApi.sendGet.argsForCall[0][0]).toMatch(/DP_API\/download_comments*/);
    });

    it('should throw an error when the first argument is none of the following: articles, news, downloads', () => {
      expect(() => Comments.load('weird')).toThrow();
    });
  });

  describe('loadCommentsToValidateCounts()', () => {

    it('should load counts of comments to validate', () => {
      spyOn(DpApi, 'sendGet');
      Comments.loadCommentsToValidateCounts('articles');
      expect(DpApi.sendGet.argsForCall[0][0]).toMatch(/DP_API\/article_comments\/counts*/);
    });

    it('should group counts by period_created', () => {
      spyOn(DpApi, 'sendGet');
      Comments.loadCommentsToValidateCounts('articles');
      expect(DpApi.sendGet.argsForCall[0][0]).toContain('group_by=period_created');
    });

    it('should throw an error when the first argument is none of the following: articles, news, downloads', () => {
      expect(() => {
        Comments.loadCommentsToValidateCounts('articles');
        Comments.loadCommentsToValidateCounts('news');
        Comments.loadCommentsToValidateCounts('downloads');
      }).not.toThrow();

      expect(() => Comments.loadCommentsToValidateCounts('weird')).toThrow();
    });
  });

  describe('loadCommentsToReviewCount()', () => {

    it('should load count of comments to review', () => {
      spyOn(DpApi, 'sendGet');
      Comments.loadCommentsToReviewCount('articles');
      expect(DpApi.sendGet.argsForCall[0][0]).toEqual('DP_API/article_comments/counts?is_reviewed=1');
    });

    it('should throw an error when the first argument is none of the following: articles, news, downloads', () => {
      expect(() => {
        Comments.loadCommentsToReviewCount('articles');
        Comments.loadCommentsToReviewCount('news');
        Comments.loadCommentsToReviewCount('downloads');
      }).not.toThrow();

      expect(() => Comments.loadCommentsToReviewCount('weird')).toThrow();
    });
  });

});

