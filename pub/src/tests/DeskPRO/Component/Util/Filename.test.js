jest.dontMock('DeskPRO/Component/Util/Filename.js');

describe('Filename', () => {
  const Filename = require('DeskPRO/Component/Util/Filename.js');

  describe('filenameMaxLength()', () => {
    it('smaller', () => {
      expect(Filename.filenameMaxLength('file.zip', 10)).toEqual('file.zip');
    });
    it('same length', () => {
      expect(Filename.filenameMaxLength('file.zip', 8)).toEqual('file.zip');
    });
    it('greater', () => {
      expect(Filename.filenameMaxLength('some-file.zip', 12)).toEqual('some... .zip');
    });
  });
});
