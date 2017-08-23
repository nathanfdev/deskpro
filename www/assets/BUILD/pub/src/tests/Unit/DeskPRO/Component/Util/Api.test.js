jest.dontMock('DeskPRO/Component/Util/Api.js');

describe('Api', () => {
  const Api = require('DeskPRO/Component/Util/Api.js');

  describe('replaceIds()', () => {
    it('array', () => {
      expect(Api.replaceIds(
        [
          { blob_id: 1 },
          { blob_id: 2 },
          { blob_id: 3 }
        ], 'blob_id'))
        .toEqual(
        [
            { blob_id: 1, id: 1 },
            { blob_id: 2, id: 2 },
            { blob_id: 3, id: 3 }
        ]);
    });
    it('object', () => {
      expect(Api.replaceIds(
        {
          1: { blob_id: 1 },
          2: { blob_id: 2 },
          3: { blob_id: 3 }
        }, 'blob_id'))
        .toEqual(
        {
          1: { blob_id: 1, id: 1 },
          2: { blob_id: 2, id: 2 },
          3: { blob_id: 3, id: 3 }
        });
    });
  });
});
