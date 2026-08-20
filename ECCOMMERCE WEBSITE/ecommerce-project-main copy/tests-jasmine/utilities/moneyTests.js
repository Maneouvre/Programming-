function convertCentsToDollar(priceCents){
    return (Math.round(priceCents) / 100).toFixed(2);
};

describe('test-suit-convertcentstodollar', () => {
    it('if its true', () => {
        
        expect(convertCentsToDollar(2000)).toEqual('20.00');
    });
    it('it works with zero', () => {
        
        expect(convertCentsToDollar(0)).toEqual('0.00');
    });
    it("Round off to nearest cents(200.5 to 20.01)",() => {
        expect(convertCentsToDollar(2000.5)).toEqual('20.01');
    })
});
