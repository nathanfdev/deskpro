jest.dontMock('DeskPRO/Component/Util/DisplayOrder.js');

describe('Display order service', () => {
  const DisplayOrder = require('DeskPRO/Component/Util/DisplayOrder.js');
  const Immutable = require('immutable');

  const originalCollection = new Immutable.fromJS([
    {
      id: 1,
      display_order: 1
    },
    {
      id: 2,
      display_order: 2
    },
    {
      id: 3,
      display_order: 3
    },
    {
      id: 4,
      display_order: 4
    },
    {
      id: 5,
      display_order: 5
    }
  ]);

  describe('Should return as is', () => {
    it('no params', () => {
      expect(DisplayOrder.reOrderCollection(originalCollection)).toEqual(originalCollection);
    });
    it('no changes', () => {
      expect(DisplayOrder.reOrderCollection(originalCollection, 2, 2)).toEqual(originalCollection);
    });
  });

  describe('Change order', () => {
    it('moves up', () => {
      expect(DisplayOrder.reOrderCollection(originalCollection, 2, 4).toJS()).toEqual(new Immutable.fromJS([
        {
          id: 1,
          display_order: 1
        },
        {
          id: 2,
          display_order: 4
        },
        {
          id: 3,
          display_order: 2
        },
        {
          id: 4,
          display_order: 3
        },
        {
          id: 5,
          display_order: 5
        }
      ]).toJS());
    });
    it('moves down', () => {
      expect(DisplayOrder.reOrderCollection(originalCollection, 4, 2).toJS()).toEqual(new Immutable.fromJS([
        {
          id: 1,
          display_order: 1
        },
        {
          id: 2,
          display_order: 2
        },
        {
          id: 3,
          display_order: 4
        },
        {
          id: 4,
          display_order: 3
        },
        {
          id: 5,
          display_order: 5
        }
      ]).toJS());
      expect(DisplayOrder.reOrderCollection(originalCollection, 5, 1).toJS()).toEqual(new Immutable.fromJS([
        {
          id: 1,
          display_order: 1
        },
        {
          id: 2,
          display_order: 3
        },
        {
          id: 3,
          display_order: 4
        },
        {
          id: 4,
          display_order: 5
        },
        {
          id: 5,
          display_order: 2
        }
      ]).toJS());
    });
  })
});
